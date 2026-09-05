<?php

namespace Tests\Feature\Scoping;

use App\Models\Media;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Logo and cover are picked from the library by id, so they need the same
 * ownership check as the gallery.
 */
class StoreImageScopingTest extends TestCase
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

    public function test_logo_cannot_be_set_to_an_image_from_another_store(): void
    {
        $foreignMedia = Media::factory()->for($this->theirs)->create();

        $this->putJson('/api/store/logo', ['media_id' => $foreignMedia->id])
            ->assertForbidden();

        $this->assertNull($this->mine->fresh()->logo_media_id);
    }

    public function test_cover_cannot_be_set_to_an_image_from_another_store(): void
    {
        $foreignMedia = Media::factory()->for($this->theirs)->create();

        $this->putJson('/api/store/cover', ['media_id' => $foreignMedia->id])
            ->assertForbidden();

        $this->assertNull($this->mine->fresh()->cover_media_id);
    }

    public function test_owner_only_reads_their_own_store(): void
    {
        $this->getJson('/api/store')
            ->assertOk()
            ->assertJsonPath('store.id', $this->mine->id);
    }
}
