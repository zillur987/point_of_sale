<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @implements BaseRepositoryInterface<TModel>
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /** @param TModel $model */
    public function __construct(protected readonly Model $model) {}

    public function all(array $relations = []): Collection
    {
        return $this->query()->with($relations)->get();
    }

    public function paginate(int $perPage = 15, array $filters = [], array $relations = []): LengthAwarePaginator
    {
        return $this->query()
            ->with($relations)
            ->tap(fn (Builder $q) => $this->applyFilters($q, $filters))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id, array $relations = []): ?Model
    {
        return $this->query()->with($relations)->find($id);
    }

    public function findOrFail(int $id, array $relations = []): Model
    {
        return $this->query()->with($relations)->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    public function update(Model|int $model, array $data): Model
    {
        $model = $model instanceof Model ? $model : $this->findOrFail($model);
        $model->update($data);

        return $model->refresh();
    }

    public function delete(Model|int $model): bool
    {
        $model = $model instanceof Model ? $model : $this->findOrFail($model);

        return (bool) $model->delete();
    }

    /** @return Builder<TModel> */
    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    /** Override in child repositories to add module-specific filters. */
    protected function applyFilters(Builder $query, array $filters): void {}
}
