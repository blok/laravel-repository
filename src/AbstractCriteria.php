<?php


namespace Blok\Repository;

use Blok\Repository\Contracts\RepositoryContract;

/**
 * Class AbstractCriteria
 *
 * Little helper to keep the business logic of a query in one place whatever the data changes
 *
 * @package App\Repositories
 */
abstract class Criteria
{
    /**
     * @param $model
     * @param RepositoryContract $repository
     * @return mixed
     */
    public abstract function apply($model, $repository = null);
}
