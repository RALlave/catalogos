<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use App\Models\WaitlistEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaitlistEntry>
 */
class WaitlistEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'name' => fake()->name(),
            // The table forbids the same phone twice on one product.
            'phone' => fake()->unique()->numerify('##########'),
            'notified_at' => null,
        ];
    }

    public function notified(): static
    {
        return $this->state(fn (array $attributes) => ['notified_at' => now()]);
    }
}
