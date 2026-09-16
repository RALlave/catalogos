<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Hero;
use App\Models\Media;
use App\Models\PlatformLogo;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\ImageOptimizer;
use App\Services\MediaService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Tiendas de ejemplo con datos y fotos reales.
 *
 * El seeder no lleva ni un producto adentro: todo sale de los JSON de
 * database/seeders/demo, así que corregir un precio es editar un JSON y no
 * tocar PHP.
 *
 * Es idempotente por slug de tienda. Se puede correr en producción sin
 * arrastrar un migrate:fresh: lo que existe se actualiza, lo que no, se crea.
 * Las fotos ya optimizadas no se vuelven a procesar.
 */
class DemoCatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    private string $base;

    public function __construct(
        private readonly ImageOptimizer $optimizer,
        private readonly MediaService $media,
    ) {
        $this->base = database_path('seeders/demo');
    }

    public function run(): void
    {
        $password = config('demo.password');

        foreach ($this->read('stores.json')['stores'] as $data) {
            $store = $this->store($data, $password);

            $this->command?->info("Tienda lista: {$store->slug}");

            $this->logo($store, $data['logo'] ?? null);
            $this->heroes($store, $data);
            $this->catalog($store, $data['images_dir']);

            $this->command?->info("  Panel: {$data['user']['username']} / {$password}");
        }
    }

    /**
     * La tienda y su dueño. El slug identifica: correrlo dos veces actualiza
     * los datos en lugar de crear una segunda tienda.
     *
     * @param  array<string, mixed>  $data
     */
    private function store(array $data, string $password): Store
    {
        $user = User::updateOrCreate(
            ['email' => $data['user']['email']],
            [
                'name' => $data['user']['name'],
                'username' => $data['user']['username'],
                'password' => $password,
            ],
        );

        $user->syncRoles([UserRole::StoreOwner->value]);

        $attributes = collect($data)
            ->except(['user', 'logo', 'heroes', 'images_dir', 'slug'])
            ->all();

        return Store::updateOrCreate(
            ['slug' => $data['slug']],
            $attributes + ['user_id' => $user->id, 'active' => true],
        );
    }

    /**
     * El logo sale de la galería de la plataforma, igual que cuando lo elige
     * el dueño desde el panel: se copia a la biblioteca de la tienda.
     *
     * Si ya tiene uno, se respeta — puede haberlo cambiado a mano.
     */
    private function logo(Store $store, ?string $logo): void
    {
        if ($logo === null || $store->logo_media_id !== null) {
            return;
        }

        $platform = $logo === 'default'
            ? PlatformLogo::where('is_default', true)->first() ?? PlatformLogo::orderBy('id')->first()
            : PlatformLogo::where('name', $logo)->first();

        if ($platform === null) {
            $this->command?->warn("  Sin logo: la plataforma no tiene ninguno cargado.");

            return;
        }

        $store->update(['logo_media_id' => $this->media->copyFrom($store, $platform)->id]);
    }

    /**
     * Banners del catálogo. Se saltean si la tienda ya tiene alguno, para no
     * pisar el que el dueño haya subido después.
     *
     * @param  array<string, mixed>  $data
     */
    private function heroes(Store $store, array $data): void
    {
        if ($store->heroes()->exists()) {
            return;
        }

        foreach ($data['heroes'] ?? [] as $order => $hero) {
            /* Perfil de biblioteca: el banner ocupa el ancho de la pantalla y
               necesita la variante grande, que el perfil de producto no genera. */
            $media = $this->upload($store, $hero['image'], 'library');

            if ($media === null) {
                continue;
            }

            Hero::create([
                'store_id' => $store->id,
                'media_id' => $media->id,
                'eyebrow' => $hero['eyebrow'] ?? null,
                'title' => $hero['title'],
                'text' => $hero['text'] ?? null,
                'order' => $order,
                'active' => true,
            ]);
        }
    }

    /**
     * Categorías y productos de la tienda, desde su propio JSON.
     */
    private function catalog(Store $store, string $name): void
    {
        $data = $this->read($name.'.json');

        $categories = [];

        foreach ($data['categories'] as $category) {
            $categories[$category['slug']] = Category::updateOrCreate(
                ['store_id' => $store->id, 'slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'] ?? null,
                    'order' => $category['order'] ?? 0,
                    'active' => true,
                ],
            )->id;
        }

        foreach ($data['products'] as $item) {
            $product = Product::updateOrCreate(
                ['store_id' => $store->id, 'slug' => $item['slug']],
                [
                    'category_id' => $categories[$item['category']] ?? null,
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? null,
                    'description' => $item['description'] ?? null,
                    'specs' => $item['specs'] ?? null,
                    'benefits' => $item['benefits'] ?? null,
                    'badges' => $item['badges'] ?? null,
                    'price' => $item['price'] ?? null,
                    'sale_price' => $item['sale_price'] ?? null,
                    'featured' => $item['featured'] ?? false,
                    'visible' => $item['visible'] ?? true,
                    'sold_out' => $item['sold_out'] ?? false,
                    'is_new' => $item['is_new'] ?? false,
                    'order' => $item['order'] ?? 0,
                ],
            );

            $this->gallery($store, $product, $item['images'] ?? [], $name);
        }

        $this->command?->info('  '.count($data['products']).' productos en '.count($data['categories']).' categorías');
    }

    /**
     * Galería del producto. Las fotos ya subidas se reconocen por el nombre
     * del archivo, así que volver a correr el seeder no reprocesa imágenes ni
     * duplica filas en la biblioteca.
     *
     * @param  array<int, string>  $files
     */
    private function gallery(Store $store, Product $product, array $files, string $directory): void
    {
        $order = 0;

        foreach ($files as $file) {
            /* Un archivo con carpeta adentro ("gorras/x-01.jpg") se toma tal
               cual: así una tienda puede sumar una línea de productos sin
               mezclar sus fotos con las del resto del catálogo. */
            $path = str_contains($file, '/') ? $file : $directory.'/'.$file;

            $media = $this->upload($store, $path, 'product');

            if ($media === null) {
                continue;
            }

            $product->images()->updateOrCreate(
                ['media_id' => $media->id],
                ['order' => $order],
            );

            $order++;
        }

        if ($order === 0) {
            $this->command?->warn("  Sin imágenes: {$product->slug}");
        }
    }

    /**
     * Convierte un archivo de demo/images a las variantes WebP de la tienda.
     * El nombre visible en la biblioteca es el del archivo, y es también lo
     * que hace reconocible una imagen ya cargada.
     */
    private function upload(Store $store, string $path, string $profile): ?Media
    {
        $source = $this->base.'/images/'.$path;

        if (! is_file($source)) {
            $this->command?->warn("  Falta el archivo: images/{$path}");

            return null;
        }

        $name = pathinfo($path, PATHINFO_FILENAME);

        $existing = $store->media()->where('name', $name)->first();

        if ($existing !== null) {
            return $existing;
        }

        $image = $this->optimizer->optimize($source, Media::directoryFor($store->id), $profile);

        return $store->media()->create([
            'path' => $image['path'],
            'variants' => $image['variants'],
            'name' => $name,
            'alt' => $name,
            'mime' => ImageOptimizer::MIME,
            'size' => $image['size'],
            'width' => $image['width'],
            'height' => $image['height'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function read(string $file): array
    {
        $path = $this->base.'/'.$file;

        if (! is_file($path)) {
            throw new \RuntimeException("No existe database/seeders/demo/{$file}");
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }
}
