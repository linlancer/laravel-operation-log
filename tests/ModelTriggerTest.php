<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 15:28
 */
namespace LinLancer\Laravel\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use LinLancer\Laravel\OperationLoggerServiceProvider;
use LinLancer\Laravel\Tests\TestModel\TestModel;

class ModelTriggerTest extends \PHPUnit\Framework\TestCase
{
    public function init()
    {
        $manager = new Manager();
        $options = [
            'driver'    => 'mysql',
            'host'      => '127.0.0.1',
            'database'  => 'test',
            'username'  => 'root',
            'password'  => 'root1234',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => 't_',
        ];
        $manager->addConnection($options);
        $manager->setEventDispatcher(resolve(Dispatcher::class));
        $manager->setAsGlobal();
        $manager->bootEloquent();

    }

    public function testTrigger()
    {
        $this->init();
        $app = app();
        $providerMock = new OperationLoggerServiceProvider($app);
        $providerMock->boot();

        $model = new TestModel;
        $model->create(['name' => uniqid()]);
    }

}
