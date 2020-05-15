<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/14
 * Time: 17:57
 */

namespace LinLancer\Laravel\Tests;

use LinLancer\Laravel\Config;
use LinLancer\Laravel\OperationLoggerServiceProvider;
use Orchestra\Testbench\TestCase;

class ConfigTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            OperationLoggerServiceProvider::class,
        ];
    }
    public function testGetClasses()
    {
        $config = Config::getClasses(false);
        $this->assertCount(2, $config);

        $config = Config::getClasses(true);
        $this->assertCount(1, $config);
    }

    public function testGetMapping()
    {
        $mapping = Config::getMapping('class_name', 'short_tag', false);
        $this->assertArrayHasKey('App\Models\Purchase\Requisition', $mapping);
        $this->assertArrayHasKey('App\Models\Purchase\RequisitionSkuInfo', $mapping);
    }
}
