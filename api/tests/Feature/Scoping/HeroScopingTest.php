<?php

namespace Tests\Feature\Scoping;

use App\Models\Hero;
use App\Models\Media;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HeroScopingTest extends TestCase
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

    public function test_index_only_lists_own_heroes(): void
    {
        $own = Hero::factory()->for($this->mine)->create();
        $foreign = Hero::factory()->for($this->theirs)->create();

        $response = $this->getJson('/api/heroes')->assertOk();

        $ids = array_column($response->json('data'), 'id');

        $this->assertContains($own->id, $ids);
        $this->assertNotContains($foreign->id, $ids);
    }

    public function test_owner_cannot_view_another_stores_hero(): void
    {
        $foreign = Hero::factory()->for($this->theirs)->create();

        $this->getJson("/api/heroes/{$foreign->id}")->assertForbidden();
    }

    public function test_owner_cannot_update_another_stores_hero(): void
    {
        $foreign = Hero::factory()->for($this->theirs)->create();

        $this->putJson("/api/heroes/{$foreign->id}", ['title' => 'Taken over'])
            ->assertForbidden();

        $this->assertDatabaseMissing('heroes', [
            'id' => $foreign->id,
            'title' => 'Taken over',
        ]);
    }

    public function test_owner_cannot_delete_another_stores_hero(): void
    {
        $foreign = Hero::factory()->for($this->theirs)->create();

        $this->deleteJson("/api/heroes/{$foreign->id}")->assertForbidden();

        $this->assertDatabaseHas('heroes', ['id' => $foreign->id]);
    }

    public function test_reorder_rejects_heroes_of_another_store(): void
    {
        $own = Hero::factory()->for($this->mine)->create(['order' => 1]);
        $foreign = Hero::factory()->for($this->theirs)->create(['order' => 1]);

        $this->postJson('/api/heroes/reorder', ['ids' => [$own->id, $foreign->id]])
            ->assertForbidden();

        $this->assertSame(1, $foreign->fresh()->order);
    }

    public function test_hero_cannot_use_an_image_from_another_store(): void
    {
        $foreignMedia = Media::factory()->for($this->theirs)->create();

        $this->postJson('/api/heroes', [
            'title' => 'Borrowed photo',
            'media_id' => $foreignMedia->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('media_id');
    }
}
