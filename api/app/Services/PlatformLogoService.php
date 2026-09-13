<?php

namespace App\Services;

use App\Models\PlatformLogo;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * La galería de logos que el superadmin ofrece a las tiendas.
 *
 * No se cachea como la marca de la plataforma: esto se lee en dos pantallas del
 * panel y no en cada visita del catálogo.
 */
class PlatformLogoService
{
    public function __construct(private readonly ImageOptimizer $optimizer) {}

    /**
     * @return Collection<int, PlatformLogo>
     */
    public function all(): Collection
    {
        return PlatformLogo::orderByDesc('id')->get();
    }

    /**
     * El archivo se convierte a WebP con las tres medidas de la biblioteca: un
     * logo elegido puede terminar en cualquier tamaño del catálogo.
     */
    public function store(UploadedFile $file, ?string $name = null): PlatformLogo
    {
        $image = $this->optimizer->optimize($file->getRealPath(), Setting::DIRECTORY);

        return PlatformLogo::create([
            'name' => $name ?: $this->name($file),
            'path' => $image['path'],
            'variants' => $image['variants'],
            'mime' => ImageOptimizer::MIME,
            'size' => $image['size'],
            'width' => $image['width'],
            'height' => $image['height'],
        ]);
    }

    /**
     * El logo con el que arranca una tienda nueva, o null si todavía no se
     * eligió ninguno.
     */
    public function default(): ?PlatformLogo
    {
        return PlatformLogo::where('is_default', true)->first();
    }

    /**
     * Marca el logo por defecto. Hay uno solo, así que el anterior se apaga en
     * la misma transacción: dos por defecto no significan nada.
     */
    public function setDefault(PlatformLogo $logo): PlatformLogo
    {
        DB::transaction(function () use ($logo): void {
            PlatformLogo::where('is_default', true)->whereKeyNot($logo->id)->update(['is_default' => false]);

            $logo->update(['is_default' => true]);
        });

        return $logo->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PlatformLogo $logo, array $data): PlatformLogo
    {
        $logo->update($data);

        return $logo->refresh();
    }

    /**
     * Borrarlo no toca a las tiendas: la que lo eligió se quedó con su propia
     * copia en la biblioteca.
     */
    public function delete(PlatformLogo $logo): void
    {
        $path = $logo->path;
        $variants = $logo->variants;

        $logo->delete();

        $this->optimizer->forget($path, $variants);
    }

    /**
     * Nombre visible: el del archivo subido, sin extensión.
     */
    private function name(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return Str::limit($name !== '' ? $name : 'logo', 100, '');
    }
}
