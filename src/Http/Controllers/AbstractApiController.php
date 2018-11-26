<?php

namespace Blok\Repository\Http\Controllers;

use App\Http\Controllers\Controller;
use Blok\Repository\AbstractEloquentRepository;
use Blok\Repository\Contracts\ApiControllerContract;
use Blok\Repository\Traits\Modelable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class AbstractApiController
 *
 * Default route controller to don't rewriting the same things again and again
 *
 * @package App\Http\Controllers
 */
abstract class AbstractApiController extends Controller implements ApiControllerContract
{
    use Modelable;

    /**
     * @var AbstractEloquentRepository
     */
    public $model;

    public function __construct(){
        $this->app = app();
        $this->makeModel();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request)
    {
        return $this->model->paginate(50, $request->toArray());
    }

    /**
     * Show the form settings
     *
     * @return \Illuminate\Http\Response
     * @throws \ReflectionException
     */
    public function create(){
        return $this->model->getForm('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->model->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function show($id)
    {
        return $this->model->find($id);
    }

    /**
     * Show the form info
     *
     * @param $id
     * @return \Illuminate\Http\Response
     * @throws \ReflectionException
     */
    public function edit($id){
        return $this->model->getForm('edit', $id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            return $this->model->update($request->all(), $id);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            return $this->model->delete($id);
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
    }
}
