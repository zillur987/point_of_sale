<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?int $parentId = null,
        public ?string $description = null,
        public int $sortOrder = 0,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
            description: $data['description'] ?? null,
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}
