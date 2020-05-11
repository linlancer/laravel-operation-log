<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 11:52
 */

namespace LinLancer\Laravel\OperationLog\Events;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use LinLancer\Laravel\EloquentModel;
use LinLancer\Laravel\OperationLog\Handlers\BaseEventHandler;

class ModelTriggerEvent extends Event
{
    private $clientIp;

    private $baseEventHandler;

    public function __construct(Request $request, BaseEventHandler $baseEventHandler)
    {
        $this->clientIp = $request->getClientIp();
        $this->baseEventHandler = $baseEventHandler;
    }

    public function updated(EloquentModel $obj)
    {
//        echo $this->clientIp.'数据已更新'.PHP_EOL;
        $this->baseEventHandler->handle('updated', $obj, $this->clientIp);
    }

    public function created(EloquentModel $obj)
    {
//        echo $this->clientIp.'数据已添加'.PHP_EOL;
        $this->baseEventHandler->handle('created', $obj, $this->clientIp);
    }

    public function deleted(EloquentModel $obj)
    {
//        echo $this->clientIp.'数据已删除'.PHP_EOL;
        $this->baseEventHandler->handle('deleted', $obj, $this->clientIp);
    }

    public function forceDeleted(EloquentModel $obj)
    {
//        echo $this->clientIp.'强制删除'.PHP_EOL;
        $this->baseEventHandler->handle('forceDeleted', $obj, $this->clientIp);
    }

}