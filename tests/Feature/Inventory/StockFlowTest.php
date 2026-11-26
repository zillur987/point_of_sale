<?php

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Needs the Step 2 factories: Product, ProductVariant, Unit (+ the stock-related columns
 * track_stock / alert_quantity on products and allow_decimal on units).
 * Permissions are bypassed with Gate::before; swap for your own role seeding if you prefer.
 */
class StockFlowTest extends TestCase
{
    use RefreshDatabase;

    private Warehouse $main;
    private Warehouse $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());

        $this->main = $this->postJson('/api/v1/warehouses', ['name' => 'Main', 'code' => 'main'])
            ->assertCreated()->json('data');
        $this->branch = $this->postJson('/api/v1/warehouses', ['name' => 'Branch', 'code' => 'br1'])
            ->assertCreated()->json('data');
    }

    private function variant(bool $decimal = false, bool $track = true): ProductVariant
    {
        $unit = Unit::factory()->create(['allow_decimal' => $decimal]);
        $product = Product::factory()->create(['unit_id' => $unit->id, 'track_stock' => $track]);

        return ProductVariant::factory()->create(['product_id' => $product->id]);
    }

    private function adjust(int $warehouseId, int $variantId, string $direction, float|int $qty, string $reason = 'correction')
    {
        return $this->postJson('/api/v1/stock/adjustments', [
            'warehouse_id' => $warehouseId,
            'reason' => $reason,
            'items' => [['product_variant_id' => $variantId, 'direction' => $direction, 'quantity' => $qty]],
        ]);
    }

    public function test_first_warehouse_becomes_default_and_default_cannot_be_deleted(): void
    {
        $this->assertTrue($this->main['is_default']);
        $this->assertFalse($this->branch['is_default']);

        $this->deleteJson("/api/v1/warehouses/{$this->main['id']}")->assertStatus(422);
    }

    public function test_opening_stock_updates_balance_and_writes_ledger(): void
    {
        $variant = $this->variant();

        $this->adjust($this->main['id'], $variant->id, 'in', 10, 'opening_stock')->assertCreated();

        $this->assertDatabaseHas('stock_levels', ['warehouse_id' => $this->main['id'], 'product_variant_id' => $variant->id, 'quantity' => '10.000']);
        $this->assertDatabaseHas('stock_movements', ['type' => 'opening', 'quantity' => '10.000', 'balance_after' => '10.000']);
    }

    public function test_cannot_remove_more_than_available(): void
    {
        $variant = $this->variant();
        $this->adjust($this->main['id'], $variant->id, 'in', 3)->assertCreated();

        $this->adjust($this->main['id'], $variant->id, 'out', 5)->assertStatus(422)
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'Insufficient stock'));

        $this->assertDatabaseHas('stock_levels', ['product_variant_id' => $variant->id, 'quantity' => '3.000']);
        $this->assertDatabaseCount('stock_adjustments', 1); // failed document rolled back
    }

    public function test_opening_stock_cannot_remove_stock(): void
    {
        $variant = $this->variant();

        $this->adjust($this->main['id'], $variant->id, 'out', 1, 'opening_stock')
            ->assertStatus(422)->assertJsonValidationErrors('items.0.direction');
    }

    public function test_whole_unit_products_reject_decimals_and_untracked_products_are_rejected(): void
    {
        $whole = $this->variant(decimal: false);
        $untracked = $this->variant(track: false);

        $this->adjust($this->main['id'], $whole->id, 'in', 1.5)->assertStatus(422)->assertJsonValidationErrors('items.0.quantity');
        $this->adjust($this->main['id'], $untracked->id, 'in', 1)->assertStatus(422)->assertJsonValidationErrors('items.0.product_variant_id');
    }

    public function test_decimal_units_accept_fractions(): void
    {
        $kg = $this->variant(decimal: true);

        $this->adjust($this->main['id'], $kg->id, 'in', 2.5)->assertCreated();
        $this->assertDatabaseHas('stock_levels', ['product_variant_id' => $kg->id, 'quantity' => '2.500']);
    }

    public function test_transfer_moves_stock_between_warehouses_and_writes_two_ledger_rows(): void
    {
        $variant = $this->variant();
        $this->adjust($this->main['id'], $variant->id, 'in', 10)->assertCreated();

        $this->postJson('/api/v1/stock/transfers', [
            'from_warehouse_id' => $this->main['id'],
            'to_warehouse_id' => $this->branch['id'],
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 4]],
        ])->assertCreated();

        $this->assertDatabaseHas('stock_levels', ['warehouse_id' => $this->main['id'], 'product_variant_id' => $variant->id, 'quantity' => '6.000']);
        $this->assertDatabaseHas('stock_levels', ['warehouse_id' => $this->branch['id'], 'product_variant_id' => $variant->id, 'quantity' => '4.000']);
        $this->assertDatabaseHas('stock_movements', ['type' => 'transfer_out', 'quantity' => '-4.000']);
        $this->assertDatabaseHas('stock_movements', ['type' => 'transfer_in', 'quantity' => '4.000']);
    }

    public function test_transfer_to_same_warehouse_or_beyond_stock_is_rejected(): void
    {
        $variant = $this->variant();
        $this->adjust($this->main['id'], $variant->id, 'in', 2)->assertCreated();

        $this->postJson('/api/v1/stock/transfers', [
            'from_warehouse_id' => $this->main['id'], 'to_warehouse_id' => $this->main['id'],
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors('to_warehouse_id');

        $this->postJson('/api/v1/stock/transfers', [
            'from_warehouse_id' => $this->main['id'], 'to_warehouse_id' => $this->branch['id'],
            'items' => [['product_variant_id' => $variant->id, 'quantity' => 9]],
        ])->assertStatus(422);

        $this->assertDatabaseCount('stock_transfers', 0);
        $this->assertDatabaseHas('stock_levels', ['warehouse_id' => $this->main['id'], 'quantity' => '2.000']);
    }

    public function test_stock_list_summary_and_low_stock_filter(): void
    {
        $variant = $this->variant();
        $variant->product->update(['alert_quantity' => 5]);
        $this->adjust($this->main['id'], $variant->id, 'in', 3)->assertCreated();

        $this->getJson('/api/v1/stock?low_stock=1')->assertOk()->assertJsonPath('data.0.is_low', true);
        $this->getJson('/api/v1/stock/summary')->assertOk()->assertJsonPath('data.low_stock_count', 1);
        $this->getJson('/api/v1/stock/movements')->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_warehouse_with_history_cannot_be_deleted(): void
    {
        $variant = $this->variant();
        $this->adjust($this->branch['id'], $variant->id, 'in', 1)->assertCreated();

        $this->deleteJson("/api/v1/warehouses/{$this->branch['id']}")->assertStatus(422);
    }
}
