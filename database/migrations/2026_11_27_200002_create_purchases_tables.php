<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('purchases',function(Blueprint $t){$t->id();$t->string('purchase_no',50)->unique();$t->foreignId('supplier_id')->constrained()->restrictOnDelete();$t->foreignId('warehouse_id')->constrained()->restrictOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('status',20)->default('draft')->index();$t->unsignedBigInteger('subtotal')->default(0);$t->unsignedBigInteger('discount_amount')->default(0);$t->unsignedBigInteger('tax_amount')->default(0);$t->unsignedBigInteger('grand_total')->default(0);$t->text('note')->nullable();$t->timestamp('ordered_at')->nullable();$t->timestamp('received_at')->nullable();$t->timestamps();$t->index(['supplier_id','status']);});
  Schema::create('purchase_items',function(Blueprint $t){$t->id();$t->foreignId('purchase_id')->constrained()->cascadeOnDelete();$t->foreignId('product_variant_id')->constrained()->restrictOnDelete();$t->string('product_name');$t->string('variant_name')->nullable();$t->string('sku')->index();$t->decimal('quantity',14,3);$t->unsignedBigInteger('unit_cost');$t->unsignedBigInteger('line_total');$t->timestamps();$t->index(['purchase_id','product_variant_id']);});
 }
 public function down(): void {Schema::dropIfExists('purchase_items');Schema::dropIfExists('purchases');}
};
