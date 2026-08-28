<?php

namespace App\Services;

use App\Models\Hero;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class HeroService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Store $store, array $data): Hero
    {
        $data['order'] = $data['order'] ?? $this->nextOrder($store);

        // Refreshed so the database defaults (active) reach the response.
        return $store->heroes()->create($data)->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Hero $hero, array $data): Hero
    {
        $hero->update($data);

        return $hero->refresh();
    }

    public function delete(Hero $hero): void
    {
        $hero->delete();
    }

    /**
     * Hand out the `order` values these heroes already had, following the new
     * sequence: the same swap the categories use.
     *
     * @param  array<int, int>  $ids
     */
    public function reorder(Store $store, array $ids): void
    {
        DB::transaction(function () use ($store, $ids): void {
            $this->normalizeOrder($store);

            $slots = $store->heroes()
                ->whereIn('id', $ids)
                ->orderBy('order')
                ->pluck('order')
                ->all();

            foreach ($ids as $position => $id) {
                Hero::where('id', $id)->update(['order' => $slots[$position]]);
            }
        });
    }

    /**
     * The swap needs every hero to hold a different `order`, so a store with
     * repeated values is rewritten as a 1..N sequence before touching anything.
     */
    private function normalizeOrder(Store $store): void
    {
        $orders = $store->heroes()->pluck('order');

        if ($orders->count() === $orders->unique()->count()) {
            return;
        }

        $ids = $store->heroes()->orderBy('order')->orderBy('id')->pluck('id');

        foreach ($ids as $position => $id) {
            Hero::where('id', $id)->update(['order' => $position + 1]);
        }
    }

    private function nextOrder(Store $store): int
    {
        return (int) $store->heroes()->max('order') + 1;
    }
}
