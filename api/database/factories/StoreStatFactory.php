<?php

namespace Database\Factories;

use App\Enums\StatType;
use App\Models\Store;
use App\Models\StoreStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreStat>
 */
class StoreStatFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'product_id' => null,
            'type' => StatType::Visit,
            'date' => today(),
            'count' => fake()->numberBetween(1, 50),
        ];
    }
}
