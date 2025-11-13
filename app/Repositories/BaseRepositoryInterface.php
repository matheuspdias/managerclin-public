<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface BaseRepositoryInterface
{
    /**
     * @return TModel|null
     */
    public function findById(int $id): ?Model;

    /**
     * @return TModel|null
     */
    public function findByCriteria(array $criteria): ?Model;

    /**
     * @return Collection<int, TModel>
     */
    public function getAll(): Collection;

    /**
     * @return Collection<int, TModel>
     */
    public function getByUser(int $idUser): Collection;

    /**
     * @return LengthAwarePaginator<TModel>
     */
    public function paginate(?string $search, int $page, int $perPage, ?string $order = null): LengthAwarePaginator;

    /**
     * @param object $dto
     * @return TModel
     */
    public function store(object $dto): Model;

    /**
     * @param object $dto
     * @return TModel|null
     */
    public function update(int $id, object $dto): Model;

    public function deleteById(int $id): void;
}
