<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 10:41
 */
namespace LinLancer\Laravel;

use Illuminate\Support\ServiceProvider;
use LinLancer\Laravel\OperationLog\Events\ModelTriggerEvent;

class OperationLoggerServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/operation_logger.php',
            'operation_logger'
        );
        EloquentModel::observe(ModelTriggerEvent::class);
    }

    public function boot():void
    {

    }

}