<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface BaseRepositoryInterface
{
    /** @return Collection<int, TModel> */
    public function all(array $relations = []): Collection;

    /** @return LengthAwarePaginator<TModel> */
    public function paginate(int $perPage = 15, array $filters = [], array $relations = []): LengthAwarePaginator;

    /** @return TModel|null */
    public function find(int $id, array $relations = []): ?Model;

    /** @return TModel */
    public function findOrFail(int $id, array $relations = []): Model;

    /** @return TModel */
    public function create(array $data): Model;

    /** @param TModel|int $model  @return TModel */
    public function update(Model|int $model, array $data): Model;

    /** @param TModel|int $model */
    public function delete(Model|int $model): bool;
}
