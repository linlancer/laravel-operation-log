<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 10:57
 */

namespace LinLancer\Laravel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use LinLancer\Laravel\OperationLog\QueryBuilder\EloquentQueryBuilder;
use LinLancer\Laravel\OperationLog\Traits\ModelAttributes;
use LinLancer\Laravel\OperationLog\Traits\TriggerEvent;
use Illuminate\Database\Eloquent\Concerns\HasEvents;


class EloquentModel extends Model
{
    use ModelAttributes,TriggerEvent,HasEvents {
        TriggerEvent::fireModelEvent insteadof HasEvents;
    }

    const DATA_FORMAT = 'Y-m-d H:i:s';

    protected $error;

    protected $extra;
    
    /**
     * 日志记录所依赖的方法  获取属性值
     * @param        $key
     * @param string $value
     * @param array  $fieldEnum
     * @return mixed
     */
    public function getFormatAttributeValue($key, $value = '', $fieldEnum = [])
    {
        $value = empty($value) ? $this->getAttributeFromArray($key) : $value;
        return $this->mutateAttributes($key, $value, $fieldEnum);
    }

    /**
     * 日志记录所要用到的属性转换方法
     * @param       $key
     * @param       $value
     * @param array $fieldEnum
     * @return mixed
     */
    public function mutateAttributes($key, $value, $fieldEnum = [])
    {
        if (method_exists($this, 'get'.Str::studly($key).'Attribute'))
            return $this->{'get'.Str::studly($key).'Attribute'}($value);
        elseif (method_exists($this, 'format'.Str::studly($key).'Attribute'))
            return $this->{'format'.Str::studly($key).'Attribute'}($value);
        elseif (!empty($fieldEnum))
            return $fieldEnum[$value] ?? $value;
        else
            return $value;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|Model|Builder
     */
    public function newEloquentBuilder($query)
    {
        return new EloquentQueryBuilder($query);
    }

    /**
     * @param      $event
     * @param bool $halt
     * @return mixed
     */
    public function fireModelEventFromBuilder($event, $halt = true)
    {
        return $this->fireModelEvent($event, $halt);
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setExtra($key, $value)
    {
        $this->extra[$key] = $value;
    }

    /**
     * @param $key
     * @return null
     */
    public function getExtra($key)
    {
        return $this->extra[$key] ?? null;
    }

    /**
     * 获取所有的关联的模型
     * @param string $heritage
     * @param EloquentModel|null $model
     * @return \ReflectionMethod[]
     * @throws \ReflectionException
     */
    public function getAllRelations($heritage = 'all', EloquentModel $model = null)
    {
        if (is_null($model))
            $model = $this;

        $modelName = get_class($model);
        $types = [
            'children' => 'Has',
            'parents' => 'Belongs',
            'all' => ''
        ];

        $heritage = in_array($heritage, array_keys($types)) ? $heritage : 'all';

        $reflectionClass = new \ReflectionClass($model);
        $traits = $reflectionClass->getTraits();
        $traitMethodNames = [];
        foreach ($traits as $name => $trait) {
            $traitMethods = $trait->getMethods();
            foreach ($traitMethods as $traitMethod) {
                $traitMethodNames[] = $traitMethod->getName();
            }
        }
        $currentMethod = collect(explode('::', __METHOD__))->last();
        $filter = $types[$heritage];
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methods = collect($methods)->filter(function ($method) use ($modelName, $traitMethodNames, $currentMethod) {
            $methodName = $method->getName();
            if (!in_array($methodName, $traitMethodNames)
                && strpos($methodName, '__') !== 0
                && $method->class === $modelName
                && !$method->isStatic()
                && $methodName != $currentMethod
            ) {
                $parameters = (new \ReflectionMethod($modelName, $methodName))->getParameters();
                return collect($parameters)->filter(function ($parameter) {
                    return !$parameter->isOptional();
                })->isEmpty();
            }
            return false;
        })->mapWithKeys(function ($method) use ($model, $filter) {
            $methodName = $method->getName();
            /**
             * @var HasOneOrMany|BelongsTo $relation
             */
            $relation = $model->$methodName();
            if (is_subclass_of($relation, Relation::class)) {
                $type = (new \ReflectionClass($relation))->getShortName();
                if (!$filter || strpos($type, $filter) === 0) {
                    return [
                        $methodName => [
                            'class' => get_class($relation->getRelated()),
                            'relation' => get_class($relation) ,
                            'table_name' => $relation->getRelated()->getTable(),
                            'method_name' => $methodName,
                            'foreign_key' => $relation->getForeignKeyName(),
                            'owner_key' => method_exists($relation, 'getLocalKeyName')
                                ? $relation->getLocalKeyName()
                                : $relation->getOwnerKeyName()

                        ]
                    ];
                }
            }
            return [];
        })->toArray();
        return collect($methods);
    }

    /**
     * 根据给定的类名在当前类中查找关联关系
     * @param string $className
     * @return mixed
     * @throws \ReflectionException
     */
    public function getRelationByGivenClass(string $className = '')
    {
        if (empty($className))
            $className = get_class($this);
        $methods = $this->getAllRelations('children', $this);
        return $methods->where('class', $className)->first();
    }
}