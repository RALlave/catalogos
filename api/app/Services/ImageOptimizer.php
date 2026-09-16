<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Turns an uploaded file into the set of WebP variants the catalog serves.
 * The original is never stored: what reaches the disk is only what a browser
 * is going to download.
 */
class ImageOptimizer
{
    private const DISK = 'public';

    private const EXTENSION = 'webp';

    public const MIME = 'image/webp';

    private readonly ImageManager $manager;

    public function __construct()
    {
        // GD ships enabled with WebP support; Imagick is not required.
        $this->manager = ImageManager::usingDriver(new Driver());
    }

    /**
     * Writes one file per variant and reports what the media row needs.
     *
     * `path` is the biggest variant, the one every existing caller already
     * reads. Width, height and size describe that same file.
     *
     * @param  string  $source  absolute path to the image being read
     * @param  string  $directory  destination folder, relative to the disk
     * @return array{variants: array<string, array{path: string, width: int, height: int}>, path: string, size: int, width: int, height: int}
     */
    public function optimize(string $source, string $directory, string $profile = 'library'): array
    {
        $image = $this->manager->decodePath($source)->orient();

        return $this->write($image, $directory, $this->sizes($profile));
    }

    /**
     * Cuts a rectangle out of an already optimized file and writes it again as
     * the given variants. The rectangle is in pixels of the source file.
     *
     * @param  array<int, string>  $names  variants to generate, e.g. the ones the media already had
     * @param  array{x: int, y: int, width: int, height: int}  $rect
     * @return array{variants: array<string, array{path: string, width: int, height: int}>, path: string, size: int, width: int, height: int}
     */
    public function crop(string $source, string $directory, array $names, array $rect): array
    {
        $image = $this->manager->decodePath($source);

        $image->crop($rect['width'], $rect['height'], $rect['x'], $rect['y']);

        return $this->write($image, $directory, $this->sizesNamed($names));
    }

    /**
     * @param  array<string, int>  $sizes  biggest first
     * @return array{variants: array<string, array{path: string, width: int, height: int}>, path: string, size: int, width: int, height: int}
     */
    private function write(ImageInterface $image, string $directory, array $sizes): array
    {
        $encoder = new WebpEncoder(quality: (int) config('media.quality'));
        $base = Str::random(40);

        $variants = [];
        $largest = null;
        $byWidth = [];

        /* Biggest first: each step scales down the previous result instead of
           decoding the source again. */
        foreach ($sizes as $name => $max) {
            $image->scaleDown($max, $max);

            $width = $image->width();

            /* A photo narrower than two targets comes out identical in both.
               The second one reuses the first file instead of duplicating it. */
            if (isset($byWidth[$width])) {
                $variants[$name] = $byWidth[$width];

                continue;
            }

            $encoded = (string) $image->encode($encoder);
            $path = $directory.'/'.$base.'_'.$name.'.'.self::EXTENSION;

            Storage::disk(self::DISK)->put($path, $encoded);

            $variant = [
                'path' => $path,
                'width' => $width,
                'height' => $image->height(),
            ];

            $variants[$name] = $variant;
            $byWidth[$width] = $variant;

            $largest ??= $variant + ['size' => strlen($encoded)];
        }

        return [
            'variants' => $variants,
            'path' => $largest['path'],
            'size' => $largest['size'],
            'width' => $largest['width'],
            'height' => $largest['height'],
        ];
    }

    /**
     * Removes every file the media points at, variants included. Older rows
     * only have `path`, so it is deleted too.
     *
     * @param  array<string, array{path: string, width: int, height: int}>|null  $variants
     */
    public function forget(string $path, ?array $variants): void
    {
        $paths = array_column($variants ?? [], 'path');
        $paths[] = $path;

        Storage::disk(self::DISK)->delete(array_values(array_unique($paths)));
    }

    /**
     * The sizes of a profile, biggest first.
     *
     * @return array<string, int>
     */
    private function sizes(string $profile): array
    {
        $sizes = config('media.sizes');
        $names = config('media.profiles.'.$profile) ?? array_keys($sizes);

        return $this->sizesNamed($names);
    }

    /**
     * The given sizes, biggest first. Unknown names are ignored.
     *
     * @param  array<int, string>  $names
     * @return array<string, int>
     */
    private function sizesNamed(array $names): array
    {
        $selected = array_intersect_key(config('media.sizes'), array_flip($names));

        arsort($selected);

        return $selected;
    }
}
