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

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
