<?php

namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface;
use App\Traits\ThrowsExceptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @template TModel of Model
 * @implements BaseRepositoryInterface<TModel>
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    use ThrowsExceptions;

    /** @var TModel */
    protected Model $model;

    protected array $searchable = [];
    protected array $sortable = [];
    protected array $relations = [];

    /**
     * @param TModel $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return TModel|null
     */
    public function findById(int $id): ?Model
    {
        $query = $this->getQuery();

        if (!empty($this->relations)) {
            foreach ($this->relations as $relation) {
                $query->with($relation);
            }
        }

        return $query->find($id);
    }

    /**
     * @return TModel|null
     */
    public function findByCriteria(array $criteria): ?Model
    {
        $query = $this->getQuery();
        return $query->where($criteria)->first();
    }

    /**
     * @return Collection<int, TModel>
     */
    public function getAll(): Collection
    {
        $query = $this->getQuery();

        if (!empty($this->relations)) {
            foreach ($this->relations as $relation) {
                $query->with($relation);
            }
        }

        return $query->get();
    }

    /**
     * @return Collection<int, TModel>
     */
    public function getByUser(int $idUser): Collection
    {
        $query = $this->getQuery();

        if (!empty($this->relations)) {
            foreach ($this->relations as $relation) {
                $query->with($relation);
            }
        }

        $query->where('id_user', $idUser);

        return $query->get();
    }

    /**
     * @return LengthAwarePaginator<TModel>
     */
    public function paginate(?string $search, int $page, int $perPage, ?string $order = null): LengthAwarePaginator
    {
        $query = $this->getQuery();

        if ($search && !empty($this->searchable)) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        if ($order) {
            [$field, $direction] = explode(':', $order) + [null, null];

            if (!in_array($field, $this->sortable)) {
                $this->throwDomain("Invalid order field: {$field}.");
            }

            if (!in_array(strtolower($direction), ['asc', 'desc'])) {
                $this->throwDomain("Invalid order direction: {$direction}. Use 'asc' or 'desc'.");
            }

            $query->orderBy($field, $direction);
        }

        if (!empty($this->relations)) {
            foreach ($this->relations as $relation) {
                $query->with($relation);
            }
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param object $dto
     * @return TModel
     */
    public function store(object $dto): Model
    {
        $data = $this->filterDtoToArray($dto);

        $query = $this->getQuery();

        $created = $query->create($data);
        $created->refresh();
        return $created;
    }

    /**
     * @param object $dto
     * @return TModel|null
     */
    public function update(int $id, object $dto): Model
    {
        $record = $this->getQuery()->find($id);
        if (!$record) {
            $this->throwNotFound("Registro com ID {$id} não encontrado na empresa atual.");
        }

        $record->update($this->filterDtoToArray($dto));
        return $record;
    }

    public function deleteById(int $id): void
    {
        $record = $this->getQuery()->find($id);
        if (!$record) {
            $this->throwNotFound("Registro com ID {$id} não encontrado na empresa atual.");
        }

        $record->delete();
    }

    protected function filterDtoToArray(object $dto): array
    {
        return array_filter(get_object_vars($dto), fn($value) => !is_null($value));
    }

    /**
     * aplica o escopo de id_company nas models que não extende o BaseModel(User) é preciso ter o método forCurrentCompany na model
     * @return \Illuminate\Database\Eloquent\Builder<TModel>
     */
    protected function getQuery()
    {
        if (method_exists($this->model, 'forCurrentCompany')) {
            return $this->model::forCurrentCompany();
        }

        return $this->model->newQuery();
    }
}
