<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * The gallery is a pivot: the caller usually passes a product and a media
     * of the same store, because these defaults create one of each.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'media_id' => Media::factory(),
            'order' => 1,
        ];
    }
}
