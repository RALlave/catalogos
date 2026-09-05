<?php

namespace Tests\Feature\Scoping;

use App\Models\Order;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderScopingTest extends TestCase
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

    public function test_index_only_lists_own_orders(): void
    {
        $own = Order::factory()->for($this->mine)->create();
        $foreign = Order::factory()->for($this->theirs)->create();

        $response = $this->getJson('/api/orders')->assertOk();

        $ids = array_column($response->json('data'), 'id');

        $this->assertContains($own->id, $ids);
        $this->assertNotContains($foreign->id, $ids);
    }

    public function test_owner_cannot_view_another_stores_order(): void
    {
        $foreign = Order::factory()->for($this->theirs)->create();

        $this->getJson("/api/orders/{$foreign->id}")->assertForbidden();
    }

    /**
     * The ranking joins order_items, which has no store_id of its own: the
     * scoping lives in the join and is easy to lose.
     */
    public function test_most_requested_products_ignore_other_stores(): void
    {
        $this->item($this->mine, 'Own product', 2);
        $this->item($this->theirs, 'Foreign product', 50);

        $names = array_column(
            $this->getJson('/api/orders/top')->assertOk()->json('products'),
            'name'
        );

        $this->assertSame(['Own product'], $names);
    }

    private function item(Store $store, string $name, int $quantity): void
    {
        Order::factory()->for($store)->create()->items()->create([
            'name' => $name,
            'price' => 1000,
            'quantity' => $quantity,
        ]);
    }
}
