<?php

namespace Tests\Feature\Scoping;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The gallery is where two stores could end up sharing a file, so every entry
 * point has to check the media belongs to the same store as the product.
 */
class ProductImageScopingTest extends TestCase
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

    public function test_owner_cannot_attach_an_image_from_another_store(): void
    {
        $product = Product::factory()->for($this->mine)->create();
        $foreignMedia = Media::factory()->for($this->theirs)->create();

        $this->postJson("/api/products/{$product->id}/images/attach", [
            'media_ids' => [$foreignMedia->id],
        ])->assertForbidden();

        $this->assertDatabaseCount('product_images', 0);
    }

    public function test_owner_cannot_attach_images_to_another_stores_product(): void
    {
        $foreignProduct = Product::factory()->for($this->theirs)->create();
        $ownMedia = Media::factory()->for($this->mine)->create();

        $this->postJson("/api/products/{$foreignProduct->id}/images/attach", [
            'media_ids' => [$ownMedia->id],
        ])->assertForbidden();

        $this->assertDatabaseCount('product_images', 0);
    }

    public function test_owner_cannot_delete_an_image_from_another_stores_product(): void
    {
        $foreignProduct = Product::factory()->for($this->theirs)->create();
        $image = ProductImage::factory()->create([
            'product_id' => $foreignProduct->id,
            'media_id' => Media::factory()->for($this->theirs)->create()->id,
        ]);

        $this->deleteJson("/api/products/{$foreignProduct->id}/images/{$image->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('product_images', ['id' => $image->id]);
    }

    /**
     * The image belongs to another product, so its own product check must fire
     * even though the caller does own the product in the URL.
     */
    public function test_owner_cannot_delete_an_image_through_a_different_product(): void
    {
        $product = Product::factory()->for($this->mine)->create();
        $other = Product::factory()->for($this->mine)->create();

        $image = ProductImage::factory()->create([
            'product_id' => $other->id,
            'media_id' => Media::factory()->for($this->mine)->create()->id,
        ]);

        $this->deleteJson("/api/products/{$product->id}/images/{$image->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('product_images', ['id' => $image->id]);
    }

    public function test_reorder_rejects_images_of_another_stores_product(): void
    {
        $product = Product::factory()->for($this->mine)->create();
        $foreignProduct = Product::factory()->for($this->theirs)->create();

        $foreignImage = ProductImage::factory()->create([
            'product_id' => $foreignProduct->id,
            'media_id' => Media::factory()->for($this->theirs)->create()->id,
            'order' => 1,
        ]);

        $this->postJson("/api/products/{$product->id}/images/reorder", [
            'ids' => [$foreignImage->id],
        ])->assertForbidden();

        $this->assertSame(1, $foreignImage->fresh()->order);
    }
}
