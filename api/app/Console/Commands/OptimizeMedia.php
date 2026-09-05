<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Rebuilds the WebP variants of images already in the library. Rows uploaded
 * before the conversion keep a single unoptimized file, and the sizes may
 * change later on, so this is the way to bring the disk back in line with the
 * current config.
 */
class OptimizeMedia extends Command
{
    protected $signature = 'media:optimize
                            {--profile=library : Which set of variants to build}
                            {--all : Rebuild every image, not only the ones without variants}
                            {--keep : Leave the source files on disk}';

    protected $description = 'Convert library images to the WebP variants of a profile';

    public function handle(ImageOptimizer $optimizer): int
    {
        $profile = (string) $this->option('profile');

        if (! config('media.profiles.'.$profile)) {
            $this->error(sprintf('Unknown profile [%s].', $profile));

            return self::FAILURE;
        }

        $query = Media::query()->when(! $this->option('all'), fn ($query) => $query->whereNull('variants'));

        $total = $query->count();

        if ($total === 0) {
            $this->info('Nothing to convert.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Converting %d image(s) with the [%s] profile.', $total, $profile));

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $before = 0;
        $after = 0;
        $failed = [];

        $query->each(function (Media $media) use ($optimizer, $profile, &$before, &$after, &$failed, $bar): void {
            try {
                $before += $this->weigh($media);
                $after += $this->convert($media, $optimizer, $profile);
            } catch (Throwable $exception) {
                $failed[] = sprintf('#%d %s — %s', $media->id, $media->name, $exception->getMessage());
            }

            $bar->advance();
        });

        $bar->finish();
        $this->newLine(2);

        $this->report($before, $after, $failed);

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Builds the new files and points the row at them. The old ones are removed
     * afterwards, so a failure halfway through leaves the media untouched.
     *
     * The output always lands in the store folder, which also pulls in the
     * images that older uploads scattered across other directories.
     */
    private function convert(Media $media, ImageOptimizer $optimizer, string $profile): int
    {
        $source = Storage::disk('public')->path($media->path);

        if (! is_file($source)) {
            throw new \RuntimeException('the file is missing from the disk');
        }

        $previous = [$media->path, ...array_column($media->variants ?? [], 'path')];

        $image = $optimizer->optimize($source, $media->directory(), $profile);

        $media->update([
            'path' => $image['path'],
            'variants' => $image['variants'],
            'mime' => ImageOptimizer::MIME,
            'size' => $image['size'],
            'width' => $image['width'],
            'height' => $image['height'],
        ]);

        if (! $this->option('keep')) {
            $keep = array_column($image['variants'], 'path');

            Storage::disk('public')->delete(array_values(array_diff(array_unique($previous), $keep)));
        }

        return $this->weigh($media->refresh());
    }

    /**
     * Bytes the media takes on disk, counting every variant.
     */
    private function weigh(Media $media): int
    {
        $paths = array_column($media->variants ?? [], 'path');
        $paths[] = $media->path;

        $disk = Storage::disk('public');

        return array_sum(array_map(
            fn (string $path) => $disk->exists($path) ? $disk->size($path) : 0,
            array_unique($paths)
        ));
    }

    /**
     * @param  array<int, string>  $failed
     */
    private function report(int $before, int $after, array $failed): void
    {
        $this->line(sprintf('Before: %s', $this->human($before)));
        $this->line(sprintf('After:  %s', $this->human($after)));

        if ($before > 0) {
            $this->info(sprintf('Saved %s (%d%%).', $this->human($before - $after), round((1 - $after / $before) * 100)));
        }

        if ($failed === []) {
            return;
        }

        $this->newLine();
        $this->error(sprintf('%d image(s) could not be converted:', count($failed)));

        foreach ($failed as $failure) {
            $this->line('  '.$failure);
        }
    }

    private function human(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1).' MB'
            : number_format($bytes / 1024, 1).' KB';
    }
}
