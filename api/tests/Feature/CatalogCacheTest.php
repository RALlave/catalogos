<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * La caché sirve si ahorra consultas y si el dueño ve sus cambios enseguida.
 * Las dos mitades importan: una caché que no se invalida es peor que no tener.
 */
class CatalogCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_visit_to_a_store_hits_no_database(): void
    {
        $store = Store::factory()->create();
        Product::factory()->for($store)->create();

        $this->getJson("/api/stores/{$store->slug}")->assertOk();

        $this->assertSame(0, $this->queriesFor(fn () => $this->getJson("/api/stores/{$store->slug}")->assertOk()));
    }

    public function test_second_visit_to_the_product_list_hits_no_database(): void
    {
        $store = Store::factory()->create();
        Product::factory()->count(3)->for($store)->create();

        $this->getJson("/api/stores/{$store->slug}/products")->assertOk();

        $this->assertSame(0, $this->queriesFor(fn () => $this->getJson("/api/stores/{$store->slug}/products")->assertOk()));
    }

    public function test_cached_response_is_identical_to_the_first_one(): void
    {
        $store = Store::factory()->create();
        Product::factory()->for($store)->create();

        $first = $this->getJson("/api/stores/{$store->slug}/products")->assertOk()->json();
        $second = $this->getJson("/api/stores/{$store->slug}/products")->assertOk()->json();

        $this->assertSame($first, $second);
    }

    public function test_saving_a_product_shows_up_right_away(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->for($store)->create(['name' => 'Old name']);

        $this->getJson("/api/stores/{$store->slug}/products")
            ->assertJsonPath('data.0.name', 'Old name');

        Sanctum::actingAs($store->user);
        $this->putJson("/api/products/{$product->id}", ['name' => 'New name'])->assertOk();

        $this->getJson("/api/stores/{$store->slug}/products")
            ->assertJsonPath('data.0.name', 'New name');
    }

    public function test_hiding_a_product_takes_it_off_the_catalog_right_away(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->for($store)->create();

        $this->getJson("/api/stores/{$store->slug}/products")->assertJsonCount(1, 'data');

        Sanctum::actingAs($store->user);
        $this->putJson("/api/products/{$product->id}", ['name' => $product->name, 'visible' => false])->assertOk();

        $this->getJson("/api/stores/{$store->slug}/products")->assertJsonCount(0, 'data');
    }

    /**
     * El reordenamiento escribe con `update` masivo, que no dispara los eventos
     * del modelo: si la invalidación a mano se cae, esto lo delata.
     */
    public function test_reordering_products_shows_up_right_away(): void
    {
        $store = Store::factory()->create();
        $first = Product::factory()->for($store)->create(['name' => 'First', 'order' => 1]);
        $second = Product::factory()->for($store)->create(['name' => 'Second', 'order' => 2]);

        $this->getJson("/api/stores/{$store->slug}/products")
            ->assertJsonPath('data.0.name', 'First');

        Sanctum::actingAs($store->user);
        $this->postJson('/api/products/reorder', ['ids' => [$second->id, $first->id]])->assertOk();

        $this->getJson("/api/stores/{$store->slug}/products")
            ->assertJsonPath('data.0.name', 'Second');
    }

    public function test_renaming_a_category_shows_up_right_away(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->for($store)->create(['name' => 'Old name']);

        $this->getJson("/api/stores/{$store->slug}")
            ->assertJsonPath('store.categories.0.name', 'Old name');

        Sanctum::actingAs($store->user);
        $this->putJson("/api/categories/{$category->id}", ['name' => 'New name'])->assertOk();

        $this->getJson("/api/stores/{$store->slug}")
            ->assertJsonPath('store.categories.0.name', 'New name');
    }

    public function test_each_store_has_its_own_cache(): void
    {
        $mine = Store::factory()->create();
        $theirs = Store::factory()->create();

        Product::factory()->for($mine)->create(['name' => 'Mine']);
        Product::factory()->for($theirs)->create(['name' => 'Theirs']);

        $this->getJson("/api/stores/{$mine->slug}/products")->assertJsonPath('data.0.name', 'Mine');
        $this->getJson("/api/stores/{$theirs->slug}/products")->assertJsonPath('data.0.name', 'Theirs');
    }

    /**
     * Texto libre: una entrada por término dejaría el disco a merced de un bot
     * pidiendo `?search=aaa`, `?search=aab`…
     */
    public function test_searches_are_not_cached(): void
    {
        $store = Store::factory()->create();
        Product::factory()->for($store)->create(['name' => 'Blue lamp']);

        $this->getJson("/api/stores/{$store->slug}/products?search=lamp")->assertJsonCount(1, 'data');

        $this->assertGreaterThan(0, $this->queriesFor(
            fn () => $this->getJson("/api/stores/{$store->slug}/products?search=lamp")->assertOk()
        ));
    }

    /**
     * Un parámetro que no es del catálogo (el `utm_source` que pega WhatsApp)
     * entraría en los enlaces de paginación de la respuesta guardada.
     */
    public function test_unknown_query_parameters_are_not_cached(): void
    {
        $store = Store::factory()->create();
        Product::factory()->for($store)->create();

        $this->getJson("/api/stores/{$store->slug}/products?utm_source=whatsapp")->assertOk();

        $this->assertGreaterThan(0, $this->queriesFor(
            fn () => $this->getJson("/api/stores/{$store->slug}/products?utm_source=whatsapp")->assertOk()
        ));
    }

    private function queriesFor(callable $callback): int
    {
        $queries = 0;

        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        $callback();

        return $queries;
    }
}
