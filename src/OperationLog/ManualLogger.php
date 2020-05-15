<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/14
 * Time: 17:09
 */

namespace LinLancer\Laravel\OperationLog;

use Illuminate\Http\Request;
use LinLancer\Laravel\EloquentModel;
use LinLancer\Laravel\OperationLog\Handlers\BaseEventHandler;
use LinLancer\Laravel\OperationLogger;

/**
 * Class ManualLogger
 * @package LinLancer\Laravel\OperationLog
 * @method static update(EloquentModel $model, string $desc)
 * @method static delete(EloquentModel $model, string $desc)
 * @method static add(EloquentModel $model, string $desc)
 */
class ManualLogger
{
    private static $instance;

    private $baseEventHandler;

    private $request;

    private $clientIp;

    public function __construct(BaseEventHandler $baseEventHandler, Request $request)
    {
        $this->baseEventHandler = $baseEventHandler;
        $this->request = $request;
        $this->clientIp = $this->request->getClientIp();
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = resolve(self::class);
        }
        return self::$instance;
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        $instance = self::getInstance();
        return call_user_func_array([$instance, $name], $args);
    }

    /**
     * @param $name
     * @param $arguments
     * @return ManualLogger
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        /**
         * @var EloquentModel $model
         */
        list($model, $desc) = $arguments;
        if (!$model instanceof OperationLogger || $model->exists === false) {
            throw new \Exception('Method Only Receive One Parameter and must implement OperationLogger and extends EloquentModel and real exist!');
        }
        switch ($name) {
            case 'update':
                $this->baseEventHandler->handleManually('updated',$model, $this->clientIp, $desc);
                break;
            case 'delete':
                $this->baseEventHandler->handleManually('deleted',$model, $this->clientIp, $desc);
                break;
            case 'add':
                $this->baseEventHandler->handleManually('created',$model, $this->clientIp, $desc);
                break;
            default:
                break;
        }
        return self::getInstance();

    }
}