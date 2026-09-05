<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'items_count' => fake()->numberBetween(1, 5),
            'total' => fake()->randomFloat(2, 100, 9999),
            'currency' => 'USD',
        ];
    }
}
