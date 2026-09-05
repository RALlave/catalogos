<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'store_id',
    'category_id',
    'name',
    'slug',
    'sku',
    'description',
    'specs',
    'benefits',
    'badges',
    'price',
    'sale_price',
    'featured',
    'visible',
    'sold_out',
    'is_new',
    'order',
])]
class Product extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'benefits' => 'array',
            'badges' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'featured' => 'boolean',
            'visible' => 'boolean',
            'sold_out' => 'boolean',
            'is_new' => 'boolean',
            'order' => 'integer',
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
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }
}
