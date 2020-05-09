<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 11:22
 */

namespace LinLancer\Laravel\OperationLog\Traits;

use LinLancer\Laravel\EloquentModel;
use LinLancer\Laravel\OperationLogger;

trait TriggerEvent
{
    /**
     * Fire the given event for the model.
     *
     * @param  string  $event
     * @param  bool  $halt
     * @return mixed
     */
    protected function fireModelEvent($event, $halt = true)
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }

        // First, we will get the proper method to call on the event dispatcher, and then we
        // will attempt to fire a custom, object based event for the given event. If that
        // returns a result we can return that result, or we'll call the string events.
        $method = $halt ? 'until' : 'dispatch';

        $result = $this->filterModelEventResults(
            $this->fireCustomModelEvent($event, $method)
        );

        if ($result === false) {
            return false;
        }
        $className = static::class;
        if ($this instanceof OperationLogger)
            $className = EloquentModel::class;

        return ! empty($result) ? $result
            : static::$dispatcher->{$method}(
                "eloquent.{$event}: " . $className, $this
            );
    }
}