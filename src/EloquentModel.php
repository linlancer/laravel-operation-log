<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 10:57
 */

namespace LinLancer\Laravel;

use Illuminate\Database\Eloquent\Model;
use LinLancer\Laravel\OperationLog\Traits\TriggerEvent;
use Illuminate\Database\Eloquent\Concerns\HasEvents;


class EloquentModel extends Model
{
    use TriggerEvent {
        TriggerEvent::fireModelEvent insteadof HasEvents;
    }
}