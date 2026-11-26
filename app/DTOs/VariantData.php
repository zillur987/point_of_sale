<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class VariantData
{
    /** @param array<string, string>|null $attributes */
    public function __construct(
        public string $name,
        public string $sku,
        public int $costPrice,
        public int $salePrice,
        public ?int $id = null,
        public ?string $barcode = null,
        public ?array $attributes = null,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? 'Default',
            sku: strtoupper(trim($data['sku'])),
            costPrice: (int) $data['cost_price'],
            salePrice: (int) $data['sale_price'],
            id: isset($data['id']) ? (int) $data['id'] : null,
            barcode: filled($data['barcode'] ?? null) ? trim($data['barcode']) : null,
            attributes: $data['attributes'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'cost_price' => $this->costPrice,
            'sale_price' => $this->salePrice,
            'attributes' => $this->attributes,
            'is_active' => $this->isActive,
        ];
    }
}
