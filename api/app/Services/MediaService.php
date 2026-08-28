<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    private const DISK = 'public';

    /**
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, Media>
     */
    public function storeMany(Store $store, array $files): Collection
    {
        return new Collection(array_map(fn (UploadedFile $file) => $this->store($store, $file), $files));
    }

    public function store(Store $store, UploadedFile $file): Media
    {
        // Se leen antes de mover el archivo: después la ruta temporal ya no existe.
        $size = @getimagesize($file->getRealPath()) ?: [];

        return $store->media()->create([
            'path' => $file->store('media/'.$store->id, self::DISK),
            'name' => $this->name($file),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $size[0] ?? null,
            'height' => $size[1] ?? null,
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

        $media->delete();

        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
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
