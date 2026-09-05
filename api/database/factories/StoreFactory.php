<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->sentence(),
            'industry' => fake()->word(),
            'whatsapp' => fake()->numerify('##########'),
            'currency' => 'USD',
            'active' => true,
        ];
    }

    /**
     * A store the public catalog must not serve.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }
}
