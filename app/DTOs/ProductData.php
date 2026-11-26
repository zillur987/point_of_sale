<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ProductType;

final readonly class ProductData
{
    /** @param list<VariantData> $variants */
    public function __construct(
        public string $name,
        public ProductType $type,
        public int $unitId,
        public array $variants,
        public ?int $categoryId = null,
        public ?int $brandId = null,
        public ?string $description = null,
        public bool $trackStock = true,
        public string|float|int $alertQuantity = 0,
        public int $taxRateBps = 0,
        public bool $taxInclusive = false,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: ProductType::from($data['type']),
            unitId: (int) $data['unit_id'],
            variants: array_map(VariantData::fromArray(...), array_values($data['variants'])),
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            brandId: isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            description: $data['description'] ?? null,
            trackStock: (bool) ($data['track_stock'] ?? true),
            alertQuantity: $data['alert_quantity'] ?? 0,
            taxRateBps: (int) ($data['tax_rate_bps'] ?? 0),
            taxInclusive: (bool) ($data['tax_inclusive'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    /** Attributes for the products table (variants are persisted separately). */
    public function productAttributes(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'category_id' => $this->categoryId,
            'brand_id' => $this->brandId,
            'unit_id' => $this->unitId,
            'description' => $this->description,
            'track_stock' => $this->trackStock,
            'alert_quantity' => $this->alertQuantity,
            'tax_rate_bps' => $this->taxRateBps,
            'tax_inclusive' => $this->taxInclusive,
            'is_active' => $this->isActive,
        ];
    }
}
