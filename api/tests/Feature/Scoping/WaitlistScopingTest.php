<?php

namespace Tests\Feature\Scoping;

use App\Models\Product;
use App\Models\Store;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Waitlist rows hold a third party's name and phone: a leak here is personal
 * data of someone who never signed up with the other store.
 */
class WaitlistScopingTest extends TestCase
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

    public function test_index_only_lists_own_entries(): void
    {
        $own = $this->entry($this->mine);
        $foreign = $this->entry($this->theirs);

        $response = $this->getJson('/api/waitlist')->assertOk();

        $ids = array_column($response->json('data'), 'id');

        $this->assertContains($own->id, $ids);
        $this->assertNotContains($foreign->id, $ids);
    }

    public function test_owner_cannot_mark_another_stores_entry_as_notified(): void
    {
        $foreign = $this->entry($this->theirs);

        $this->patchJson("/api/waitlist/{$foreign->id}/notified")->assertForbidden();

        $this->assertNull($foreign->fresh()->notified_at);
    }

    public function test_owner_cannot_delete_another_stores_entry(): void
    {
        $foreign = $this->entry($this->theirs);

        $this->deleteJson("/api/waitlist/{$foreign->id}")->assertForbidden();

        $this->assertDatabaseHas('waitlist_entries', ['id' => $foreign->id]);
    }

    private function entry(Store $store): WaitlistEntry
    {
        return WaitlistEntry::factory()->for($store)->create([
            'product_id' => Product::factory()->for($store),
        ]);
    }
}
