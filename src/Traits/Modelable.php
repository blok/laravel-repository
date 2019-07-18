<?php

namespace Blok\Repository\Traits;

/**
 * Trait Modelable helped to handle class that needs to set or get a param based on a model
 *
 * @package App\Traits
 */
trait Modelable
{
    public $model;

    /**
     * Child class should always implements at least a model method and return a class name
     *
     * @return mixed
     */
    abstract public function model();

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function makeModel() {

        $model = $this->app->make($this->model());

        return $this->model = $model;
    }
}
