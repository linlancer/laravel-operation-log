<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 14:03
 */

namespace LinLancer\Laravel\OperationLog\Models;

use Illuminate\Support\Collection;
use LinLancer\Laravel\EloquentModel;

class OperationLogModel extends EloquentModel
{
    public $fillable = [
        'trigger_class',
        'associated_id',
        'associated_value',
        'user_id',
        'client_ip',
        'trigger_time',
        'event_desc',
        'change_content',
    ];

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('operation_logger.log_table_name');
        $this->connection = config('operation_logger.log_connection');
    }
    
    public function userInfo()
    {
        return $this->belongsTo(...config('operation_logger.related_user_model'));
    }

    /**
     * 根据类名和主键获取日志记录
     * @param $className
     * @param $primaryId
     */
    public function getLogRecord($className, $primaryId)
    {
        $configs = collect(config('operation_logger.register_class'));
        $mapping = [];
        foreach ($configs as $config) {
            $mapping[$config['short_tag_en']] = $config['class_name'];
        }
        
        $className = $mapping[$className] ?? '';
        $class = $configs
            ->where('class_name', $className)
            ->first();
        $relations = collect($class['related_with'] ?? [])
            ->pluck('class_name')
            ->toArray();
        /**
         * @var Collection $records
         */
        $records = $this->where('trigger_class', $className)
            ->where('associated_id', $primaryId)
            ->get();
        if ($records->isEmpty())
            return $records;
        if (empty($records->first()->associated_value))
            return $records;
        /**
         * @var Collection $relatedRecords
         */
        $relatedRecords = $this->where('associated_value', $records->first()->associated_value)
            ->whereIn('trigger_class', $relations)
            ->get();
        $mergeCollections = $records->merge($relatedRecords);
        if (config('operation_logger.order_by', 'asc') === 'asc')
            $mergeCollections = $mergeCollections->sortBy('trigger_time');
        else
            $mergeCollections = $mergeCollections->sortByDesc('trigger_time');
        return $mergeCollections;
    }

}