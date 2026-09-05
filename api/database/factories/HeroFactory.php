<?php

namespace Database\Factories;

use App\Models\Hero;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hero>
 */
class HeroFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'media_id' => null,
            'eyebrow' => fake()->words(2, true),
            'title' => fake()->sentence(4),
            'text' => fake()->sentence(10),
            'order' => 1,
            'active' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }
}
