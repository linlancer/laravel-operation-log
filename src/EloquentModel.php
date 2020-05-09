<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 10:57
 */

namespace LinLancer\Laravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LinLancer\Laravel\OperationLog\Traits\TriggerEvent;
use Illuminate\Database\Eloquent\Concerns\HasEvents;


class EloquentModel extends Model
{
    use TriggerEvent,HasEvents {
        TriggerEvent::fireModelEvent insteadof HasEvents;
    }

    const DATA_FORMAT = 'Y-m-d H:i:s';

    protected $error;

    protected $extra;

    /**
     * 日志记录所依赖的方法  获取属性值
     * @param        $key
     * @param string $value
     * @return mixed
     */
    public function getFormatAttributeValue($key, $value = '') {
        $value = empty($value) ? $this->getAttributeFromArray($key) : $value;
        return $this->mutateAttributes($key, $value);
    }

    /**
     * 日志记录所要用到的属性转换方法
     * @param $key
     * @param $value
     * @return mixed
     */
    public function mutateAttributes($key, $value)
    {
        if (method_exists($this, 'get'.Str::studly($key).'Attribute'))
            return $this->{'get'.Str::studly($key).'Attribute'}($value);
        elseif (method_exists($this, 'format'.Str::studly($key).'Attribute'))
            return $this->{'format'.Str::studly($key).'Attribute'}($value);
        else
            return $value;
    }


    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setExtra($key, $value)
    {
        $this->extra[$key] = $value;
    }

    public function getExtra($key)
    {
        return $this->extra[$key] ?? null;
    }
}