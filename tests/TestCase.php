<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 17:15
 */

namespace LinLancer\Laravel\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;


abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $path = realpath(__DIR__.'/../');
        $app = new Application(
            $path
        );

        /*
        |--------------------------------------------------------------------------
        | Bind Important Interfaces
        |--------------------------------------------------------------------------
        |
        | Next, we need to bind some important interfaces into the container so
        | we will be able to resolve them when needed. The kernels serve the
        | incoming requests to this application from both the web and CLI.
        |
        */

        $app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \App\Http\Kernel::class
        );

        $app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \App\Console\Kernel::class
        );

        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \App\Exceptions\Handler::class
        );


        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        Hash::setRounds(4);

        return $app;
    }
}