<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class UnitData
{
    public function __construct(
        public string $name,
        public string $shortName,
        public bool $allowDecimal = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            shortName: $data['short_name'],
            allowDecimal: (bool) ($data['allow_decimal'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'short_name' => $this->shortName,
            'allow_decimal' => $this->allowDecimal,
        ];
    }
}
