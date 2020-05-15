<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/15
 * Time: 13:46
 */

namespace LinLancer\Laravel\OperationLog;

use Illuminate\Database\DatabaseServiceProvider;
use LinLancer\Laravel\OperationLoggerServiceProvider;
use LinLancer\Laravel\Tests\TestModel\TestModel;
use Orchestra\Testbench\TestCase;

class ManualLoggerTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'    => 'mysql',
            'host'      => '127.0.0.1',
            'database'  => 'test',
            'username'  => 'root',
            'password'  => 'root1234',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => 't_',
        ]);
        return [
            OperationLoggerServiceProvider::class,
            DatabaseServiceProvider::class,
        ];

    }

    protected function getPackageAliases($app)
    {
        return [
            'DB' => \Illuminate\Support\Facades\DB::class,
        ];
    }


    public function testAdd()
    {
        $model = new TestModel();
        $add = $model->create([
            'name' => uniqid('las'),
            'sex' => rand(0,9),
            'content' => uniqid('content')
        ]);
        $return = ManualLogger::add($add, '我测试了新增手动日志');
        $this->assertInstanceOf(ManualLogger::class, $return);
    }

    public function testUpdate()
    {
        $model = new TestModel();
        $find = $model->where('id', 1)->first();
        $return = ManualLogger::update($find, '我测试了更新手动日志');
        $this->assertInstanceOf(ManualLogger::class, $return);
    }

    public function testDelete()
    {
        $model = new TestModel();
        $find = $model->where('id', 1)->first();
        $return = ManualLogger::delete($find, '我测试了删除手动日志');
        $this->assertInstanceOf(ManualLogger::class, $return);
    }
}
