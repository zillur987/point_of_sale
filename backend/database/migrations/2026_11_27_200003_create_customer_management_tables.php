<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->string('name', 150);
            $table->string('email', 180)->nullable()->index();
            $table->string('phone', 40)->nullable()->index();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('points_balance')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['customer_group_id', 'is_active']);
            $table->index(['name', 'phone']);
        });

        Schema::create('customer_loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->integer('points_change');
            $table->unsignedBigInteger('balance_after');
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_loyalty_transactions');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_groups');
    }
};
