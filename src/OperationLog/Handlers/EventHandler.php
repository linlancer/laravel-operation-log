<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 14:37
 */

namespace LinLancer\Laravel\OperationLog\Handlers;


use LinLancer\Laravel\EloquentModel;

interface EventHandler
{
    public function handle(string $event, EloquentModel $model, string $clientIp = '');

    public function getChangeContent(EloquentModel $model, $original = []);

    public function getEventLog():array;
}