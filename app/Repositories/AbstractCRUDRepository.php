<?php

namespace App\Repositories;


use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractCRUDRepository
{
    protected Model $model;

    protected App $app;

    /**
     * Constructor to bind model to repository
     *
     * @param App $app
     * @throws \Exception
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    abstract public function model(): string;

    /**
     * Instantiate the model.
     *
     * @return Model
     * @throws \Exception
     */
    public function makeModel(): Model
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Model not found");
        }

        $this->model = $model;
        return $model;
    }
    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }
    public function update(array $data): Model
    {
        $model = $this->findOrFail($data['id']);
        $model->fill($data);
        $model->save();
        return $model;
    }


    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->findOrFail($id, $columns);
    }
}
