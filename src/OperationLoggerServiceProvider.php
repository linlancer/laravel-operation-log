<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 10:41
 */
namespace LinLancer\Laravel;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LinLancer\Laravel\OperationLog\Events\ModelTriggerEvent;
use LinLancer\Laravel\OperationLog\Models\OperationLogModel;

class OperationLoggerServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/operation_logger.php',
            'operation_logger'
        );
    }

    public function boot():void
    {
        EloquentModel::observe(ModelTriggerEvent::class);
        if (config('operation_logger.short_route', false))
            $this->initOperationLoggerRoute();
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom([
                __DIR__.'/../database/migrations/2020_05_13_100428_create_operation_logger_table.php'
            ]);

            $this->publishes([
                __DIR__.'/../config/operation_logger.php' => config_path('operation_logger.php'),
            ], 'config');
        }
    }

    public function initOperationLoggerRoute()
    {
        $shortTags = array_filter(array_column(config('operation_logger.register_class'), 'short_tag_en'));
        Route::group([
                'prefix' => config('operation_logger.route_prefix'),
                'middleware' => config('operation_logger.route_middleware')
            ],
            function() use ($shortTags) {
                $operationLogModel = new OperationLogModel;
                foreach ($shortTags as $shortTag) {
                    Route::get($shortTag . '/{bizCode}', function ($bizCode) use ($operationLogModel, $shortTag) {
                        return $operationLogModel->getLogRecord($shortTag, $bizCode);
                    });
                }
            });
    }
}