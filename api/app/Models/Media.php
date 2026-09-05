<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'store_id',
    'path',
    'variants',
    'name',
    'alt',
    'mime',
    'size',
    'width',
    'height',
])]
class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Productos que muestran esta imagen. La biblioteca la usa para avisar a
     * quién afecta un borrado.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_images')->withPivot('order');
    }

    /**
     * Heroes showing this image. Deleting the file only leaves them without a
     * photo, but the library warns about it first.
     *
     * @return HasMany<Hero, $this>
     */
    public function heroes(): HasMany
    {
        return $this->hasMany(Hero::class);
    }

    /**
     * Every file of a store lives in the same folder, so removing the store is
     * removing one directory.
     */
    public static function directoryFor(int $storeId): string
    {
        return 'media/'.$storeId;
    }

    public function directory(): string
    {
        return self::directoryFor($this->store_id);
    }

    /**
     * URL of one variant. Asking for a size that was not generated falls back
     * to the biggest available file, so a product image picked for the banner
     * still resolves — stretched, but never broken.
     */
    public function url(?string $size = null): string
    {
        $path = $this->variants[$size]['path'] ?? $this->path;

        return Storage::disk('public')->url($path);
    }

    /**
     * Everything a responsive <img> needs. The browser picks a file from the
     * srcset, so `src` is only the fallback; `thumb` is the small one the panel
     * grids and the cart use, where a srcset would be overkill.
     *
     * Widths are the real ones: a photo smaller than a target is not scaled up,
     * and declaring a width it does not have makes the browser choose badly.
     *
     * @return array{src: string, srcset: string, thumb: string, width: int|null, height: int|null}
     */
    public function responsive(): array
    {
        return [
            'src' => $this->url(),
            'srcset' => $this->srcset(),
            'thumb' => $this->url('thumb'),
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    /**
     * Candidates for the browser, narrowest first. Rows that predate the WebP
     * conversion have no variants and get an empty srcset: the <img> falls back
     * to `src` on its own.
     */
    public function srcset(): string
    {
        $candidates = [];

        foreach ($this->variants ?? [] as $variant) {
            $candidates[$variant['width']] = Storage::disk('public')->url($variant['path']).' '.$variant['width'].'w';
        }

        // Keyed by width: a photo too small to fill two targets wrote the same
        // size twice and would repeat a descriptor, which is invalid.
        ksort($candidates);

        return implode(', ', $candidates);
    }
}
