<?php

namespace Blok\Repository;

use Blok\Repository\Commands\ControllerApiMakeCommand;
use Blok\Repository\Commands\CriteriaMakeCommand;
use Blok\Repository\Commands\RepositoryMakeCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RepositoryMakeCommand::class,
                ControllerApiMakeCommand::class,
                CriteriaMakeCommand::class,
            ]);
        }
    }

    public function register()
    {

    }
}
