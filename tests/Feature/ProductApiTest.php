<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use App\Services\BarcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

    private function singlePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Mineral Water 500ml',
            'type' => 'single',
            'unit_id' => Unit::factory()->create()->id,
            'variants' => [
                ['sku' => 'wat-500', 'cost_price' => 1500, 'sale_price' => 2000],
            ],
        ], $overrides);
    }

    public function test_it_creates_a_single_product_with_a_generated_barcode(): void
    {
        $response = $this->postJson('/api/v1/products', $this->singlePayload())
            ->assertCreated()
            ->assertJsonPath('data.type', 'single')
            ->assertJsonCount(1, 'data.variants')
            ->assertJsonPath('data.variants.0.sku', 'WAT-500')
            ->assertJsonPath('data.variants.0.sale_price', 2000);

        $barcode = $response->json('data.variants.0.barcode');

        $this->assertSame(13, strlen($barcode));
        $this->assertStringStartsWith('200', $barcode);
        $this->assertSame(
            (int) substr($barcode, -1),
            app(BarcodeService::class)->checkDigit(substr($barcode, 0, 12))
        );
    }

    public function test_it_creates_a_variable_product_with_many_variants(): void
    {
        $payload = $this->singlePayload([
            'name' => 'T-Shirt',
            'type' => 'variable',
            'variants' => [
                ['name' => 'Red / M', 'sku' => 'TS-R-M', 'cost_price' => 30000, 'sale_price' => 50000, 'attributes' => ['color' => 'Red', 'size' => 'M']],
                ['name' => 'Red / L', 'sku' => 'TS-R-L', 'cost_price' => 30000, 'sale_price' => 50000, 'attributes' => ['color' => 'Red', 'size' => 'L']],
            ],
        ]);

        $this->postJson('/api/v1/products', $payload)
            ->assertCreated()
            ->assertJsonCount(2, 'data.variants');
    }

    public function test_a_single_product_cannot_have_multiple_variants(): void
    {
        $payload = $this->singlePayload();
        $payload['variants'][] = ['sku' => 'X-2', 'cost_price' => 1, 'sale_price' => 2];

        $this->postJson('/api/v1/products', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('variants');
    }

    public function test_sku_must_be_unique_and_prices_must_be_integers(): void
    {
        ProductVariant::factory()->create(['sku' => 'WAT-500']);

        $payload = $this->singlePayload();
        $payload['variants'][0]['sale_price'] = 19.99;

        $this->postJson('/api/v1/products', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['variants.0.sku', 'variants.0.sale_price']);
    }

    public function test_update_syncs_variants(): void
    {
        $created = $this->postJson('/api/v1/products', $this->singlePayload([
            'type' => 'variable',
            'variants' => [
                ['name' => 'A', 'sku' => 'VAR-A', 'cost_price' => 100, 'sale_price' => 200],
                ['name' => 'B', 'sku' => 'VAR-B', 'cost_price' => 100, 'sale_price' => 200],
            ],
        ]))->assertCreated();

        $productId = $created->json('data.id');
        $keepId = $created->json('data.variants.0.id');
        $removeId = $created->json('data.variants.1.id');

        $this->putJson("/api/v1/products/{$productId}", $this->singlePayload([
            'type' => 'variable',
            'unit_id' => $created->json('data.unit_id'),
            'variants' => [
                ['id' => $keepId, 'name' => 'A', 'sku' => 'VAR-A', 'cost_price' => 100, 'sale_price' => 250],
                ['name' => 'C', 'sku' => 'VAR-C', 'cost_price' => 100, 'sale_price' => 300],
            ],
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data.variants')
            ->assertJsonPath('data.variants.0.sale_price', 250);

        $this->assertSoftDeleted('product_variants', ['id' => $removeId]);
    }

    public function test_it_looks_up_a_variant_by_barcode_for_pos(): void
    {
        $variant = ProductVariant::factory()->create(['barcode' => '8901234567890']);

        $this->getJson('/api/v1/variants/barcode/8901234567890')
            ->assertOk()
            ->assertJsonPath('data.id', $variant->id)
            ->assertJsonPath('data.product.id', $variant->product_id);

        $this->getJson('/api/v1/variants/barcode/0000000000000')->assertNotFound();
    }

    public function test_inactive_products_are_not_sellable_in_pos_search(): void
    {
        $product = Product::factory()->create(['name' => 'Hidden Item', 'is_active' => false]);
        ProductVariant::factory()->create(['product_id' => $product->id]);

        $this->getJson('/api/v1/variants/search?q=Hidden')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_it_soft_deletes_a_product_and_its_variants(): void
    {
        $variant = ProductVariant::factory()->create();

        $this->deleteJson("/api/v1/products/{$variant->product_id}")->assertNoContent();

        $this->assertSoftDeleted('products', ['id' => $variant->product_id]);
        $this->assertSoftDeleted('product_variants', ['id' => $variant->id]);
    }
}
