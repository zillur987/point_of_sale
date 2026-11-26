<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('suppliers',function(Blueprint $t){$t->id();$t->string('name');$t->string('company_name')->nullable();$t->string('email')->nullable();$t->string('phone',40)->nullable();$t->string('tax_number')->nullable();$t->text('address')->nullable();$t->string('city')->nullable();$t->string('country',100)->nullable();$t->text('notes')->nullable();$t->boolean('is_active')->default(true)->index();$t->timestamps();$t->index(['name','company_name']);});}
 public function down(): void {Schema::dropIfExists('suppliers');}
};
