<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductVariant> */
final class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => 'Default',
            'sku' => strtoupper(fake()->unique()->bothify('SKU-#####')),
            'barcode' => fake()->unique()->numerify('############'),
            'cost_price' => 5000,
            'sale_price' => 8000,
            'attributes' => null,
            'is_active' => true,
        ];
    }
}
