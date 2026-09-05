<?php

namespace Tests\Feature\Scoping;

use App\Enums\StatType;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StatScopingTest extends TestCase
{
    use RefreshDatabase;

    /** Un navegador cualquiera: sin user agent no se cuenta nada. */
    private const BROWSER = ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0) Firefox/130.0'];

    private Store $mine;

    private Store $theirs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mine = Store::factory()->create();
        $this->theirs = Store::factory()->create();

        Sanctum::actingAs($this->mine->user);
    }

    public function test_dashboard_only_counts_own_visits(): void
    {
        StoreStat::factory()->for($this->mine)->create(['count' => 5]);
        StoreStat::factory()->for($this->theirs)->create(['count' => 50]);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('stats.visits.total', 5);
    }

    /**
     * El ranking sale de un join con products: el scoping vive en el where de
     * la tabla de estadísticas y es fácil de perder.
     */
    public function test_most_viewed_products_ignore_other_stores(): void
    {
        $this->views($this->mine, 'Own product', 2);
        $this->views($this->theirs, 'Foreign product', 80);

        $names = array_column(
            $this->getJson('/api/dashboard')->assertOk()->json('stats.top_viewed'),
            'name'
        );

        $this->assertSame(['Own product'], $names);
    }

    public function test_visiting_the_catalog_counts_one_visit_per_visitor_and_day(): void
    {
        $this->withHeaders(self::BROWSER)->getJson("/api/stores/{$this->mine->slug}")->assertOk();
        $this->withHeaders(self::BROWSER)->getJson("/api/stores/{$this->mine->slug}")->assertOk();

        $this->assertSame(1, $this->total($this->mine, StatType::Visit));
    }

    public function test_bots_are_not_counted(): void
    {
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1'])
            ->getJson("/api/stores/{$this->mine->slug}")
            ->assertOk();

        $this->assertSame(0, $this->total($this->mine, StatType::Visit));
    }

    public function test_opening_a_product_counts_a_view_for_that_product(): void
    {
        $product = Product::factory()->for($this->mine)->create(['visible' => true]);

        $this->withHeaders(self::BROWSER)
            ->getJson("/api/stores/{$this->mine->slug}/products/{$product->slug}")
            ->assertOk();

        $this->assertDatabaseHas('store_stats', [
            'store_id' => $this->mine->id,
            'product_id' => $product->id,
            'type' => StatType::ProductView->value,
            'count' => 1,
        ]);
    }

    /**
     * Las visitas las cuenta la API sola: aceptarlas desde el navegador sería
     * dejar que cualquiera infle el número de otra tienda.
     */
    public function test_the_public_endpoint_only_accepts_shares(): void
    {
        $this->withHeaders(self::BROWSER)
            ->postJson("/api/stores/{$this->mine->slug}/track", ['type' => StatType::Visit->value])
            ->assertUnprocessable();

        $this->assertSame(0, $this->total($this->mine, StatType::Visit));
    }

    public function test_sharing_a_product_is_counted(): void
    {
        $product = Product::factory()->for($this->mine)->create();

        $this->withHeaders(self::BROWSER)
            ->postJson("/api/stores/{$this->mine->slug}/track", [
                'type' => StatType::Share->value,
                'product_slug' => $product->slug,
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('store_stats', [
            'product_id' => $product->id,
            'type' => StatType::Share->value,
            'count' => 1,
        ]);
    }

    private function views(Store $store, string $name, int $count): void
    {
        $product = Product::factory()->for($store)->create(['name' => $name]);

        StoreStat::factory()->for($store)->create([
            'product_id' => $product->id,
            'type' => StatType::ProductView,
            'count' => $count,
        ]);
    }

    private function total(Store $store, StatType $type): int
    {
        return (int) StoreStat::query()
            ->where('store_id', $store->id)
            ->where('type', $type)
            ->sum('count');
    }
}
