<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class BrandData
{
    public function __construct(
        public string $name,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(name: $data['name'], isActive: (bool) ($data['is_active'] ?? true));
    }
}
