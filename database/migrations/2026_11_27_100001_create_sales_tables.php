<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('sales',function(Blueprint $t){$t->id();$t->string('invoice_no')->unique();$t->foreignId('warehouse_id')->constrained()->restrictOnDelete();$t->unsignedBigInteger('customer_id')->nullable()->index();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('status',20)->default('completed');$t->unsignedBigInteger('subtotal');$t->unsignedBigInteger('discount_amount')->default(0);$t->unsignedBigInteger('tax_amount')->default(0);$t->unsignedBigInteger('grand_total');$t->unsignedBigInteger('paid_amount');$t->unsignedBigInteger('change_amount')->default(0);$t->text('note')->nullable();$t->uuid('request_key')->unique();$t->timestamp('sold_at');$t->timestamps();});
  Schema::create('sale_items',function(Blueprint $t){$t->id();$t->foreignId('sale_id')->constrained()->cascadeOnDelete();$t->foreignId('product_variant_id')->constrained()->restrictOnDelete();$t->string('product_name');$t->string('variant_name')->nullable();$t->string('sku')->nullable();$t->decimal('quantity',15,3);$t->unsignedBigInteger('unit_price');$t->unsignedBigInteger('discount_amount')->default(0);$t->unsignedBigInteger('tax_amount')->default(0);$t->unsignedBigInteger('line_total');$t->timestamps();});
  Schema::create('sale_payments',function(Blueprint $t){$t->id();$t->foreignId('sale_id')->constrained()->cascadeOnDelete();$t->string('method',30);$t->unsignedBigInteger('amount');$t->string('reference')->nullable();$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('sale_payments');Schema::dropIfExists('sale_items');Schema::dropIfExists('sales');}
};
