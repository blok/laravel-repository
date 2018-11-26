<?php

namespace Blok\Repository;

use Blok\Repository\Contracts\CriteriaContract;
use Blok\Repository\Contracts\RepositoryContract;
use Blok\Repository\Exceptions\RepositoryException;
use Blok\Utils\Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Validator;
use App;

abstract class AbstractEloquentRepository implements RepositoryContract, CriteriaContract
{
    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    public $app;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var Model
     */
    private $modelInstance;

    public function __construct(App $app = null, Collection $collection = null) {
        $this->app = $app ?: app();
        $this->criteria = $collection ?: new Collection();
        $this->resetScope();
        $this->makeModel();
    }

    /**
     * Child class should implements at least model method and return a class name
     *
     * @return mixed
     */
    abstract function model();

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return Model
     */
    public function getModelInstance()
    {
        return $this->modelInstance;
    }

    public function getTable(){
        /**
         * @var $model Builder
         */
        return $this->model->getModel()->getTable();
    }

    /**
     * @param mixed $model
     * @return AbstractEloquentRepository
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param array $params
     * @return mixed
     * @internal param array $columns
     * @throws \Exception
     */
    public function all($params = ['columns' => array('*')]) {
        $params = collect(Arr::mergeWithDefaultParams($params));
        $this->applyCriteria();
        return $this->model->get($params['columns']);
    }

    /**
     * The repository should return the rules to apply
     *
     * @param $type
     * @return mixed
     */
    public function getRules($type){

        if(isset(static::${$type . 'Rules'})){
            return static::${$type . 'Rules'};
        }

        return [];
    }

    /**
     * The repository should return the form data needed
     *
     * @param $type
     * @param null $id
     * @return mixed
     *
     */
    public function getForm($type, $id = null){

        $data = [
            'rules' => [],
            'model' => null,
            'schema' => [
                'fields' => []
            ],
        ];

        if(isset(static::${$type . 'Rules'})){
            $data['rules'] = static::${$type . 'Rules'};
        }

        $class = $this->model();

        if ($id) {

            $model = $this->find($id);

            if ($model) {
                $data['model'] = $model->toArray();

                foreach ($model->toArray() as $key => $value){

                    $data['schema']['fields'][$key] = [
                        'value' => $value
                    ];

                    $enumKey = $key . 'Enums';

                    if (isset($class::${$enumKey})) {
                        $data['schema']['fields'][$key]['values'] = $this->modelInstance->getEnums($key);
                    }
                }
            }

        } else {

            $fields = $this->modelInstance->getFillable();

            foreach ($fields as $key){

                $data['schema']['fields'][$key] = [
                    'model' => $key
                ];

                $enumKey = $key . 'Enums';

                if (isset($class::${$enumKey})) {
                    $data['schema']['fields'][$key]['values'] = $this->modelInstance->getEnums($key);
                }
            }
        }

        return $data;
    }

    /**
     * @param int $perPage
     * @param array $params
     * @return mixed
     * @internal param array $columns
     * @throws \Exception
     */
    public function paginate($perPage = 100, $params = ['columns' => array('*')]) {
        $params = Arr::mergeWithDefaultParams($params);
        $this->applyCriteria();
        return $this->model->paginate($perPage, $params['columns']);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data) {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @throws ValidationException|\Exception
     * @return mixed
     */
    public function update(array $data, $id, $attribute = "id") {

        $data = array_only($data, $this->getModelInstance()->getFillable());

        $model = $this->model->where($attribute, '=', $id)->first();

        if ($model) {
            if($model->update($data)){
                return $model;
            }
        }

        return false;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id) {
        return $this->model->find($id)->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return Model
     */
    public function find($id, $columns = array('*')) {
        $this->applyCriteria();
        return $this->model->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = array('*')) {
        $this->applyCriteria();
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws RepositoryException
     */
    public function makeModel() {
        $model = app()->make($this->model());

        if (!$model instanceof Model)
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        $this->modelInstance = $model;

        return $this->model = $model->newQuery();
    }

    /**
     * @return $this
     */
    public function resetScope() {
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true){
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriteria() {
        return $this->criteria;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria) {
        $this->model = $criteria->apply($this->model, $this);
        return $this;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria) {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * @return $this
     */
    public function  applyCriteria() {
        if($this->skipCriteria === true)
            return $this;

        foreach($this->getCriteria() as $criteria) {
            if($criteria instanceof Criteria)
                $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }

    public function validation($data, $rules = [])
    {
        $validator = app('validator')->make($data, $rules);

        return $validator;
    }
}
