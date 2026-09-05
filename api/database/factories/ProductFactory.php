<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'store_id' => Store::factory(),
            'category_id' => null,
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'sku' => fake()->bothify('???-####'),
            'description' => fake()->paragraph(),
            'specs' => [
                ['label' => 'Material', 'value' => fake()->word()],
            ],
            'benefits' => [fake()->sentence(4)],
            'badges' => [],
            'price' => fake()->randomFloat(2, 100, 9999),
            'sale_price' => null,
            'featured' => false,
            'visible' => true,
            'sold_out' => false,
            'is_new' => false,
            'order' => 1,
        ];
    }

    /**
     * A product the public catalog must not serve.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['visible' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['featured' => true]);
    }

    public function soldOut(): static
    {
        return $this->state(fn (array $attributes) => ['sold_out' => true]);
    }
}
