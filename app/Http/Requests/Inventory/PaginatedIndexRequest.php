<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/** Base for list endpoints: validated filters + a bounded page size. */
abstract class PaginatedIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is enforced by route middleware (can:inventory.view)
    }

    /** Filter rules, merged with the shared pagination rules. */
    abstract protected function filterRules(): array;

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            ...$this->filterRules(),
        ];
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page', 15);
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return collect($this->validated())->except(['per_page', 'page'])->all();
    }
}
