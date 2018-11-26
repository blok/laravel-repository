# Blok Repository

[![Build Status](https://travis-ci.org/blok/repository.svg?branch=master)](https://travis-ci.org/blok/repository)
[![Packagist](https://img.shields.io/packagist/v/blok/repository.svg)](https://packagist.org/packages/blok/repository)
[![Packagist](https://poser.pugx.org/blok/repository/d/total.svg)](https://packagist.org/packages/blok/repository)
[![Packagist](https://img.shields.io/packagist/l/blok/repository.svg)](https://packagist.org/packages/blok/repository)

An opiniated way to handle business logic with the Repository pattern. This package tends to give you an opiniated structure to handle your business logic inside the repository folder. It comes with handy helpers to let you use this repository inside your controller or api controller without the need to redefine the wheel everytimes.

## Installation

Install via composer

```
composer require blok/repository
```

## Usage

Blok repository is a Laravel package that will give extra functionnalities to your model and control.

### Create a repository class 

```
php artisan make:repository UserRepository
```

It will create a Repository class inside /app/Repositories/UserRepository : 

```
<?php

namespace App\Repositories;

use App\User;
use Blok\Repository\AbstractEloquentRepository;

class UsersRepository extends AbstractEloquentRepository
{
    function model()
    {
        return User::class;
    }
}
```

Without any configuration, it will handle the basic CRUD operations : 

- all
- find
- findBy
- create
- update
- delete
- getForm
- validation

Off course, you can override any of these methods and create your own inside this Repository Class

###  How to use it inside your controller ? 

Blok/Repository comes with a very handy an common ApiController structure. To use is you can do : 

````php artisan make:apicontroller UserController````

It will create an ApiController inside App/Http/Controllers/Api : 

````
<?php

namespace App\Http\Controllers\Api;

use App\Repositories\UsersRepository;
use Blok\Repository\Http\Controllers\AbstractApiController;

class UserController extends AbstractApiController
{
    function model()
    {
        return UsersRepository::class;
    }
}
````

This controller will handle directly the CRUD logic of your repository for more infos see AbstractApiController

### Adding a business logic with a Criteria class

Off course, any methods of the AbstractClass can be overriden but sometimes you just need to add somewhere your own query logic. For that we implemented a usefull patern that will help to keep your query logic in a separated class reusable anywhere. Let's give a simple exemple for the get all methods => you want to get only public users.

### Create a Criteria

``` php artisan make:criteria OnlyPublicCriteria ```

This will create a class inside /app/Repositories/Criterias/OnlyPublicCriteria

```
<?php

namespace App\Repositories\Criterias;

use Blok\Repository\AbstractCriteria;

class OnlyPublicCriteria extends AbstractCriteria
{
    public $type = "public";

    pubic function __construct($type = 'public'){
      $this->type = $type;
    }

    public function apply($model, $repository = null)
    {
        return $model->where('visibility', $this->type);
    }
}
```

### Use it inside your Repository

In your UserRepository, you can add and handle your criteria like that : 

```
public function all($columns = array('*'))
{
    if (!auth()->check()) {
        $this->pushCriteria(new OnlyPublicCriteria());
    }

    return parent::all($columns);
}
```

It will apply the condition of where visibility=public automatically to the $userRepository->all() method.

### Use it inside your Controller

In your ControllerClass, you can inject this param like that : 

```
<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!auth()->check()){
          $this->userRepository->pushCriteria(new OnlyPublicCriteria('public'));
        }
    
        $users = $this->userRepository->paginate(12, $request->all());
        
        return view('users', compact('users'));
    }
}
```

It will have the same behavior but at the Controller level. Off course, you are free to add your variables logic when you initiate the criteria (for exemple here I push the type public for the demo).

Putting this logic inside a Criteria, will help you to queue your query condition and reuse it in different Repository.

## Security

If you discover any security related issues, please [email me](daniel@cherrypulp.com) instead of using the issue tracker.

## Credits

- [Daniel Sum](https://github.com/cherrylabs/blok-repository)
- [All contributors](https://github.com/cherrylabs/blok-repository/graphs/contributors)

This package is bootstrapped with the help of
[blok/laravel-package-generator](https://github.com/cherrylabs/blok-laravel-package-generator).
