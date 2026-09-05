<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * The row only describes a file; it does not put one on disk. Tests that
     * need the actual file write it through Storage::fake().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $file = fake()->unique()->lexify('??????????').'.webp';

        return [
            'store_id' => Store::factory(),
            'path' => fn (array $attributes) => Media::directoryFor($this->storeId($attributes)).'/'.$file,
            'variants' => fn (array $attributes) => $this->variants($this->storeId($attributes), $file),
            'name' => $file,
            'alt' => fake()->sentence(3),
            'mime' => 'image/webp',
            'size' => fake()->numberBetween(20_000, 400_000),
            'width' => 1600,
            'height' => 1200,
        ];
    }

    /**
     * A row from before the WebP conversion: one file and nothing else.
     */
    public function withoutVariants(): static
    {
        return $this->state(fn (array $attributes) => ['variants' => null]);
    }

    /**
     * The three sizes the library profile generates.
     *
     * @return array<string, array{path: string, width: int, height: int}>
     */
    private function variants(int $storeId, string $file): array
    {
        $directory = Media::directoryFor($storeId);
        $sizes = ['thumb' => 400, 'card' => 800, 'full' => 1600];
        $variants = [];

        foreach ($sizes as $size => $width) {
            $variants[$size] = [
                'path' => $directory.'/'.$size.'-'.$file,
                'width' => $width,
                'height' => (int) ($width * 0.75),
            ];
        }

        return $variants;
    }

    /**
     * The store may arrive as a model, an id or a pending factory.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function storeId(array $attributes): int
    {
        return (int) ($attributes['store_id'] instanceof Store
            ? $attributes['store_id']->id
            : $attributes['store_id']);
    }
}
