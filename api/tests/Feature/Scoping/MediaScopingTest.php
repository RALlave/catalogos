<?php

namespace Tests\Feature\Scoping;

use App\Models\Media;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MediaScopingTest extends TestCase
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

    public function test_library_only_lists_own_media(): void
    {
        $own = Media::factory()->for($this->mine)->create();
        $foreign = Media::factory()->for($this->theirs)->create();

        $response = $this->getJson('/api/media')->assertOk();

        $ids = array_column($response->json('data'), 'id');

        $this->assertContains($own->id, $ids);
        $this->assertNotContains($foreign->id, $ids);
    }

    public function test_owner_cannot_view_another_stores_media(): void
    {
        $foreign = Media::factory()->for($this->theirs)->create();

        $this->getJson("/api/media/{$foreign->id}")->assertForbidden();
    }

    public function test_owner_cannot_rename_another_stores_media(): void
    {
        $foreign = Media::factory()->for($this->theirs)->create();

        $this->putJson("/api/media/{$foreign->id}", ['name' => 'Taken over'])
            ->assertForbidden();

        $this->assertDatabaseMissing('media', [
            'id' => $foreign->id,
            'name' => 'Taken over',
        ]);
    }

    /**
     * Deleting media wipes the file and cascades over every store that shows
     * it, so this is the most expensive leak of the whole library.
     */
    public function test_owner_cannot_delete_another_stores_media(): void
    {
        $foreign = Media::factory()->for($this->theirs)->create();

        $this->deleteJson("/api/media/{$foreign->id}")->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $foreign->id]);
    }
}
