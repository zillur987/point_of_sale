<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('type', 20)->default('single');
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->text('description')->nullable();
            $table->boolean('track_stock')->default(true);
            $table->decimal('alert_quantity', 15, 3)->default(0);
            $table->unsignedSmallInteger('tax_rate_bps')->default(0); // basis points: 1500 = 15%
            $table->boolean('tax_inclusive')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
