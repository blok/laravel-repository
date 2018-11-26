<?php

namespace Blok\Repository\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ControllerApiMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:apicontroller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new ApiController class based on Repository';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/apicontroller.stub';
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        if ($this->option('repository')) {
            $repository = $this->option('repository');
        } else {
            $repositoryName = "\App\\Repositories\\".str_replace("Controller", "", $class).'Repository';
            $repository = $this->ask("From which repository do you want to create the repository ?", $repositoryName);
        }

        return str_replace(['DummyClass', 'DummyRepository'], [$class, $repository], $stub);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Controllers\Api';
    }



    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name', InputArgument::REQUIRED, 'The name of the Repository'
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['repository', 'r', InputOption::VALUE_OPTIONAL, 'The base repository of this controller.'],
        ];
    }
}
