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

class ModelTriggerEvent extends Event
{
    private $clientIp;

    public function __construct(Request $request)
    {
        $this->clientIp = $request->getClientIp();
    }

    public function updated(EloquentModel $obj)
    {

    }

    public function created(EloquentModel $obj)
    {

    }

    public function deleted(EloquentModel $obj)
    {

    }

    public function forceDeleted(EloquentModel $obj)
    {

    }

    private function logEvent()
    {
        $eventLog = [

        ];
    }
}