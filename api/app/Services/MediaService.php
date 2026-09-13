<?php

namespace App\Services;

use App\Models\Media;
use App\Models\PlatformLogo;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function __construct(private readonly ImageOptimizer $optimizer) {}

    /**
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, Media>
     */
    public function storeMany(Store $store, array $files, string $profile = 'library'): Collection
    {
        return new Collection(array_map(
            fn (UploadedFile $file) => $this->store($store, $file, $profile),
            $files
        ));
    }

    /**
     * The upload never reaches the disk as it was sent: it is converted to the
     * WebP variants of the profile and the original is dropped.
     */
    public function store(Store $store, UploadedFile $file, string $profile = 'library'): Media
    {
        $image = $this->optimizer->optimize($file->getRealPath(), Media::directoryFor($store->id), $profile);

        return $store->media()->create([
            'path' => $image['path'],
            'variants' => $image['variants'],
            'name' => $this->name($file),
            'mime' => ImageOptimizer::MIME,
            'size' => $image['size'],
            'width' => $image['width'],
            'height' => $image['height'],
        ]);
    }

    /**
     * Copia un logo de la plataforma a la biblioteca de una tienda.
     *
     * Se copia y no se referencia: así el logo elegido es una imagen más de la
     * tienda —se ve en Multimedia, se puede borrar— y el superadmin puede
     * sacar el suyo de la galería sin dejar tiendas sin logo.
     *
     * Los archivos ya están convertidos: se copian tal cual, sin volver a
     * pasar por GD. Se les antepone un prefijo al nombre para que elegir dos
     * veces el mismo logo no haga que las dos filas compartan archivo.
     */
    public function copyFrom(Store $store, PlatformLogo $logo): Media
    {
        $directory = Media::directoryFor($store->id);
        $disk = Storage::disk('public');
        $prefix = Str::random(8);

        $copied = [];

        $copy = function (string $path) use ($directory, $disk, $prefix, &$copied): string {
            /* Dos variantes del mismo ancho comparten archivo en el origen:
               tienen que seguir compartiéndolo en el destino. */
            if (isset($copied[$path])) {
                return $copied[$path];
            }

            $target = $directory.'/'.$prefix.'_'.basename($path);

            $disk->copy($path, $target);

            return $copied[$path] = $target;
        };

        $variants = [];

        foreach ($logo->variants ?? [] as $name => $variant) {
            $variants[$name] = ['path' => $copy($variant['path'])] + $variant;
        }

        return $store->media()->create([
            /* Queda marcada: cuando deje de ser el logo, se limpia sola. */
            'from_platform' => true,
            'path' => $copy($logo->path),
            'variants' => $variants,
            'name' => $logo->name,
            'mime' => $logo->mime,
            'size' => $logo->size,
            'width' => $logo->width,
            'height' => $logo->height,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Media $media, array $data): Media
    {
        $media->update($data);

        return $media->refresh();
    }

    /**
     * El archivo se va con el registro. Las galerías que la usaban pierden la
     * imagen por la cascada de la base, y el logo o la portada quedan en null.
     */
    public function delete(Media $media): void
    {
        $path = $media->path;
        $variants = $media->variants;

        $media->delete();

        $this->optimizer->forget($path, $variants);
    }

    /**
     * Nombre visible en la biblioteca: el del archivo subido, sin extensión.
     */
    private function name(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return Str::limit($name !== '' ? $name : 'imagen', 255, '');
    }
}
