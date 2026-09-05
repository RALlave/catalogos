<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
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
