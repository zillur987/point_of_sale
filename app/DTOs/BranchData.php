<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class BranchData
{
    public function __construct(
        public string $name,
        public string $code,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            code: strtoupper($data['code']),
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            address: $data['address'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => $this->isActive,
        ];
    }
}
