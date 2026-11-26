<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only ledger: rows are never updated or deleted, so there is no updated_at.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->string('type', 30);
            $table->decimal('quantity', 15, 3);       // signed
            $table->decimal('balance_after', 15, 3);
            $table->nullableMorphs('reference');      // adjustment, transfer, sale, purchase...
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_variant_id', 'warehouse_id', 'created_at'], 'stock_movements_lookup_index');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
