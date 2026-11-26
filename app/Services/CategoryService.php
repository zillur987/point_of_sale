<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CategoryData;
use App\Exceptions\BusinessRuleException;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Support\SlugGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private SlugGenerator $slugs,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->categories->paginate($perPage, $filters, ['parent']);
    }

    /** @return Collection<int, Category> root categories with `children` set recursively */
    public function tree(): Collection
    {
        $all = $this->categories->all()->where('is_active', true)->sortBy(['sort_order', 'name']);
        $byParent = $all->groupBy('parent_id');

        $attach = function (Category $category) use (&$attach, $byParent): Category {
            $category->setRelation('children', ($byParent->get($category->id) ?? new Collection())->values()->each($attach));

            return $category;
        };

        return $all->whereNull('parent_id')->values()->each($attach);
    }

    public function create(CategoryData $data): Category
    {
        return DB::transaction(fn () => $this->categories->create([
            'parent_id' => $data->parentId,
            'name' => $data->name,
            'slug' => $this->slugs->unique($data->name, fn (string $s) => $this->categories->slugExists($s)),
            'description' => $data->description,
            'sort_order' => $data->sortOrder,
            'is_active' => $data->isActive,
        ]));
    }

    public function update(Category $category, CategoryData $data): Category
    {
        $this->guardAgainstCycle($category, $data->parentId);

        return DB::transaction(fn () => $this->categories->update($category, [
            'parent_id' => $data->parentId,
            'name' => $data->name,
            'description' => $data->description,
            'sort_order' => $data->sortOrder,
            'is_active' => $data->isActive,
        ]));
    }

    public function delete(Category $category): void
    {
        if ($this->categories->hasChildren($category->id)) {
            throw new BusinessRuleException('This category has sub-categories. Move or delete them first.');
        }

        if ($this->categories->hasProducts($category->id)) {
            throw new BusinessRuleException('This category is used by products and cannot be deleted.');
        }

        $this->categories->delete($category);
    }

    /** A category cannot be its own parent or sit beneath one of its own descendants. */
    private function guardAgainstCycle(Category $category, ?int $newParentId): void
    {
        $cursor = $newParentId;
        $depth = 0;

        while ($cursor !== null && $depth++ < 50) {
            if ($cursor === $category->id) {
                throw new BusinessRuleException('A category cannot be moved under itself or its own sub-category.');
            }

            $cursor = $this->categories->find($cursor)?->parent_id;
        }
    }
}
