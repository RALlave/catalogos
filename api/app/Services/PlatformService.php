<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Platform-wide settings: what belongs to the SaaS and not to a store.
 *
 * There are three logos: `auth` for the sign-in screens, `panel` for the
 * sidebar and `icon`, the square one used as the favicon and as the icon of
 * the installed app. All of them are read on every page load of every
 * subdomain, so they are cached: a value that changes once in a while should
 * not cost a query per visit. Writing one drops its own cache.
 */
class PlatformService
{
    public function __construct(private readonly ImageOptimizer $optimizer) {}

    /**
     * Every logo, ready for an <img>. A variant nobody uploaded yet is null.
     *
     * @return array<string, array{src: string, srcset: string, thumb: string, width: int|null, height: int|null}|null>
     */
    public function logos(): array
    {
        $logos = [];

        foreach (array_keys(Setting::LOGOS) as $variant) {
            $logos[$variant] = $this->logo($variant);
        }

        return $logos;
    }

    /**
     * Everything a sign-in screen or a sidebar needs to draw one logo, or null
     * when none has been uploaded. The shape matches Media::responsive().
     *
     * @return array{src: string, srcset: string, thumb: string, width: int|null, height: int|null}|null
     */
    public function logo(string $variant): ?array
    {
        $logo = $this->raw($variant);

        if (! $logo) {
            return null;
        }

        return [
            'src' => $this->url($logo['path']),
            'srcset' => $this->srcset($logo['variants'] ?? []),
            /* El ícono no tiene `thumb`: su variante chica es la de 192, la
               que el panel usa de favicon. */
            'thumb' => $this->url(
                $logo['variants']['thumb']['path']
                    ?? $logo['variants']['icon']['path']
                    ?? $logo['path']
            ),
            'width' => $logo['width'] ?? null,
            'height' => $logo['height'] ?? null,
        ];
    }

    /**
     * Replaces one logo. The previous files go with it: nothing else points at
     * them, so keeping them would only leave orphans on the disk.
     *
     * @return array{src: string, srcset: string, thumb: string, width: int|null, height: int|null}
     */
    public function saveLogo(string $variant, UploadedFile $file): array
    {
        $previous = $this->raw($variant);

        $image = $this->optimizer->optimize(
            $file->getRealPath(),
            Setting::DIRECTORY,
            $this->profile($variant),
        );

        Setting::updateOrCreate(
            ['key' => $this->key($variant)],
            ['value' => json_encode($image)],
        );

        Cache::forget($this->cacheKey($variant));

        if ($previous) {
            $this->optimizer->forget($previous['path'], $previous['variants'] ?? null);
        }

        return $this->logo($variant);
    }

    public function deleteLogo(string $variant): void
    {
        $logo = $this->raw($variant);

        if (! $logo) {
            return;
        }

        Setting::where('key', $this->key($variant))->delete();

        Cache::forget($this->cacheKey($variant));

        $this->optimizer->forget($logo['path'], $logo['variants'] ?? null);
    }

    /**
     * The file behind one size of the platform icon, or null when no icon has
     * been uploaded.
     *
     * The manifest of the installed app points at a fixed route instead of at
     * this URL: the file name changes with every upload and the manifest is a
     * static file built once.
     */
    public function iconUrl(int $size): ?string
    {
        $icon = $this->raw('icon');

        if (! $icon) {
            return null;
        }

        $variant = Setting::ICON_SIZES[$size] ?? null;
        $path = $icon['variants'][$variant]['path'] ?? null;

        return $path ? $this->url($path) : null;
    }

    /**
     * The icon is square and is measured against the sizes a manifest asks
     * for; the other two logos are drawn as wide as the layout needs.
     */
    private function profile(string $variant): string
    {
        return $variant === 'icon' ? 'icon' : 'library';
    }

    /**
     * The stored value: paths and sizes, as ImageOptimizer wrote them.
     *
     * An empty array is the "there is no logo" marker. It has to be stored,
     * because the cache treats null as a miss and would query every time.
     *
     * @return array{path: string, variants: array<string, array{path: string, width: int, height: int}>, width: int, height: int}|null
     */
    private function raw(string $variant): ?array
    {
        $key = $this->key($variant);

        $logo = Cache::rememberForever(
            $this->cacheKey($variant),
            fn (): array => json_decode(Setting::where('key', $key)->value('value') ?? '', true) ?: []
        );

        return $logo ?: null;
    }

    private function key(string $variant): string
    {
        return Setting::LOGOS[$variant];
    }

    private function cacheKey(string $variant): string
    {
        return 'platform:logo:'.$variant;
    }

    private function url(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * @param  array<string, array{path: string, width: int, height: int}>  $variants
     */
    private function srcset(array $variants): string
    {
        $candidates = [];

        foreach ($variants as $variant) {
            $candidates[$variant['width']] = $this->url($variant['path']).' '.$variant['width'].'w';
        }

        ksort($candidates);

        return implode(', ', $candidates);
    }
}
