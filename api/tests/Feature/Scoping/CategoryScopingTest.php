<?php

namespace Tests\Feature\Scoping;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryScopingTest extends TestCase
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

    public function test_index_only_lists_own_categories(): void
    {
        $own = Category::factory()->for($this->mine)->create();
        $foreign = Category::factory()->for($this->theirs)->create();

        $response = $this->getJson('/api/categories')->assertOk();

        $ids = array_column($response->json('data'), 'id');

        $this->assertContains($own->id, $ids);
        $this->assertNotContains($foreign->id, $ids);
    }

    public function test_owner_cannot_view_another_stores_category(): void
    {
        $foreign = Category::factory()->for($this->theirs)->create();

        $this->getJson("/api/categories/{$foreign->id}")->assertForbidden();
    }

    public function test_owner_cannot_update_another_stores_category(): void
    {
        $foreign = Category::factory()->for($this->theirs)->create();

        $this->putJson("/api/categories/{$foreign->id}", ['name' => 'Taken over'])
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', [
            'id' => $foreign->id,
            'name' => 'Taken over',
        ]);
    }

    public function test_owner_cannot_delete_another_stores_category(): void
    {
        $foreign = Category::factory()->for($this->theirs)->create();

        $this->deleteJson("/api/categories/{$foreign->id}")->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $foreign->id]);
    }

    public function test_reorder_rejects_categories_of_another_store(): void
    {
        $own = Category::factory()->for($this->mine)->create(['order' => 1]);
        $foreign = Category::factory()->for($this->theirs)->create(['order' => 1]);

        $this->postJson('/api/categories/reorder', ['ids' => [$own->id, $foreign->id]])
            ->assertForbidden();

        $this->assertSame(1, $foreign->fresh()->order);
    }
}
