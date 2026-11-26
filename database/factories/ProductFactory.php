<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'type' => ProductType::Single,
            'unit_id' => Unit::factory(),
            'track_stock' => true,
            'alert_quantity' => 0,
            'tax_rate_bps' => 0,
            'tax_inclusive' => false,
            'is_active' => true,
        ];
    }
}
