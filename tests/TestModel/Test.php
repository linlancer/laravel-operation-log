<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 15:50
 */

namespace LinLancer\Laravel\Tests\TestModel;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use LinLancer\Laravel\OperationLoggerServiceProvider;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
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
