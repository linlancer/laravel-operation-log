<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 14:39
 */

namespace LinLancer\Laravel\OperationLog\Handlers;

use LinLancer\Laravel\EloquentModel;

class BaseEventHandler implements EventHandler
{

    public function handle(string $event, EloquentModel $model, string $clientIp = '')
    {
        // TODO: Implement handle() method.
    }

    public function getChangeContent(EloquentModel $model, $original = [])
    {
        // TODO: Implement getChangeContent() method.
    }

    public function getEventLog(): array
    {
        // TODO: Implement getEventLog() method.
    }
}