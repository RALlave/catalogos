<?php

namespace Tests\Feature\Scoping;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * An owner must not read or touch anything from another store.
 */
class ProductScopingTest extends TestCase
{
    use RefreshDatabase;

    private Store $mine;

    private Store $theirs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mine = Store::factory()->create();
        $this->theirs = Store::factory()->create();

        Sanctum::actingAs($this->mine->user);
    }

    public function test_index_only_lists_own_products(): void
    {
        $own = Product::factory()->for($this->mine)->create();
        $foreign = Product::factory()->for($this->theirs)->create();

        $response = $this->getJson('/api/products')->assertOk();

        $ids = array_column($response->json('data'), 'id');

        $this->assertContains($own->id, $ids);
        $this->assertNotContains($foreign->id, $ids);
    }

    public function test_owner_cannot_view_another_stores_product(): void
    {
        $foreign = Product::factory()->for($this->theirs)->create();

        $this->getJson("/api/products/{$foreign->id}")->assertForbidden();
    }

    public function test_owner_cannot_update_another_stores_product(): void
    {
        $foreign = Product::factory()->for($this->theirs)->create();

        $this->putJson("/api/products/{$foreign->id}", ['name' => 'Taken over'])
            ->assertForbidden();

        $this->assertDatabaseMissing('products', [
            'id' => $foreign->id,
            'name' => 'Taken over',
        ]);
    }

    public function test_owner_cannot_delete_another_stores_product(): void
    {
        $foreign = Product::factory()->for($this->theirs)->create();

        $this->deleteJson("/api/products/{$foreign->id}")->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $foreign->id]);
    }

    public function test_owner_cannot_clone_another_stores_product(): void
    {
        $foreign = Product::factory()->for($this->theirs)->create();

        $this->postJson("/api/products/{$foreign->id}/clone")->assertForbidden();

        $this->assertDatabaseCount('products', 1);
    }

    public function test_reorder_rejects_products_of_another_store(): void
    {
        $own = Product::factory()->for($this->mine)->create(['order' => 1]);
        $foreign = Product::factory()->for($this->theirs)->create(['order' => 1]);

        $this->postJson('/api/products/reorder', ['ids' => [$own->id, $foreign->id]])
            ->assertForbidden();

        $this->assertSame(1, $foreign->fresh()->order);
    }

    public function test_product_cannot_be_created_inside_another_stores_category(): void
    {
        $foreignCategory = Category::factory()->for($this->theirs)->create();

        $this->postJson('/api/products', [
            'name' => 'Smuggled',
            'category_id' => $foreignCategory->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('category_id');
    }

    public function test_product_cannot_be_moved_into_another_stores_category(): void
    {
        $own = Product::factory()->for($this->mine)->create();
        $foreignCategory = Category::factory()->for($this->theirs)->create();

        $this->putJson("/api/products/{$own->id}", [
            'name' => $own->name,
            'category_id' => $foreignCategory->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('category_id');
    }

    public function test_user_without_a_store_gets_no_products(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/products')->assertNotFound();
    }

    public function test_guest_cannot_reach_the_products_endpoint(): void
    {
        app('auth')->forgetGuards();

        $this->getJson('/api/products')->assertUnauthorized();
    }
}
