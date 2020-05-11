<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 14:39
 */

namespace LinLancer\Laravel\OperationLog\Handlers;

use Doctrine\DBAL\Platforms\MySQL57Platform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use LinLancer\Laravel\EloquentModel;
use LinLancer\Laravel\OperationLog\Comment\ColumnComment;
use LinLancer\Laravel\OperationLog\Models\OperationLogModel;
use LinLancer\Laravel\OperationLogger;

class BaseEventHandler implements EventHandler
{
    const CACHE_KEY = 'purchase:change_log:';
    const IGNORE_LIST = [
        '_id',
        '_time',
    ];
    const CHANGE_CONTENT_KEY = 'change_content';
    const CACHE_TIME = 360;

    const EVENT_MAPPING = [
        'updated' => '编辑',
        'created' => '添加',
        'deleted' => '删除',
        'forceDeleted' => '强制删除',
    ];
    /**
     * @var \Doctrine\DBAL\Schema\MySqlSchemaManager
     */
    protected $schema;

    protected $mySQL57Platform;

    protected $shortTagMapping = [];

    protected $event;

    protected $userId;

    protected $clientIp;

    protected $triggerClass;

    protected $associatedId;

    protected $eventDescription;

    protected $changeContent;

    protected $operationLogModel;

    public function __construct(MySQL57Platform $mySQL57Platform, OperationLogModel $operationLogModel)
    {
        //数据库管理器
        $this->schema = $this->getSchemaManager();
        $this->mySQL57Platform = $mySQL57Platform;
        $this->operationLogModel = $operationLogModel;
    }

    public function handle(string $event, EloquentModel $model, string $clientIp = '')
    {
        $this->event = $event;
        $this->associatedId = $model->{$model->getKeyName()};
        $this->clientIp = $clientIp;
        $this->triggerClass = get_class($model);
        $this->shortTagMapping = $this->getModelShortTagMapping();
        /**
         * @var OperationLogger $model
         */
        $this->userId = $model->getCurrentUserId();
        $this->loadChangeContent($model);
        switch ($event) {
            case 'updated':
                break;
            case 'created':
                break;
            case 'deleted':
                break;
            case 'forceDeleted':
                break;

        }
        $log = $this->getEventLog();
        $this->operationLogModel->create($log);
    }

    public function loadChangeContent(EloquentModel $model)
    {
        $original = $model->getOriginal();
        $changes = $model->getChanges();
        //忽略清单 暂时这么操作
        $ignoreList = self::IGNORE_LIST;

        //完整表名
        $fullTable = $model->getConnection()->getTablePrefix() . $model->getTable();

        $tableDetail = $this->getTableDetailWithCache($fullTable);

        $objectName = $this->getTableNameFromDbal($tableDetail);

        $objectName = $this->shortTagMapping[$this->triggerClass] ?? $objectName;

        $columns = $tableDetail->getColumns();

        $changeContents = '';
        foreach ($changes as $field => $value) {
            $check = false;
            //过滤的不处理
            foreach ($ignoreList as $needle) {
                if (stripos($field, $needle) !== false) {
                    $check = true;
                    break;
                }
            }
            if (!$check) {
                /**
                 * @var Column $column
                 */
                $column = $columns[$field];
                $type = $column->getType()->getName();
                $comment = $column->getComment();
                $fieldName = ColumnComment::parse($comment)->getColumnName();
                $fieldEnum = ColumnComment::parse($comment)->getEnumeration();
                switch ($type) {
                    //数值型
                    case Types::INTEGER:
                    case Types::SMALLINT:
                        $value = intval($value);
                        break;
                    case Types::FLOAT:
                        $scale = $column->getScale();
                        $value = round($value, $scale);
                        break;
                    case Types::BIGINT:
                    case Types::DECIMAL:
                        $scale = $column->getScale();
                        $value = number_format($value, $scale, '.', '');
                        break;
                    //字符型
                    case Types::STRING:
                        $value = $column->getType()->convertToDatabaseValue($value, $this->mySQL57Platform);
                        break;
                    case Types::BOOLEAN:
                        $value = intval($value);
                        break;
                    //其他的暂时都不处理其变化
                    default:
                        $value = null;
                        break;
                }

                $before = '';
                if (isset($original[$field]))
                    $before = $model->getFormatAttributeValue($field, $original[$field], $fieldEnum);

                $after = is_null($value) ? null : $model->getFormatAttributeValue($field, $value, $fieldEnum);
                $changeContent = $before ? '【%s】由 %s 变为： %s；'.PHP_EOL : '【%s】更新为：%s%s；'.PHP_EOL;
                if ($before != $after && !is_null($after))
                    $changeContents .= sprintf($changeContent, $fieldName, $before, $after);
            }
        }
        $operationDescription = '【%s 了 %s】';
        $operationName = self::EVENT_MAPPING[$this->event] ?? '操作';
        $operationDescription = sprintf($operationDescription, $operationName, $objectName);
        $this->eventDescription = $operationDescription;
        $this->changeContent = $changeContents;
    }

    public function getEventLog(): array
    {
        return [
            'trigger_class' => $this->triggerClass,
            'associated_id' => $this->associatedId,
            'user_id' => $this->userId,
            'client_ip' => $this->clientIp,
            'trigger_time' => date('Y-m-d H:i:s'),
            'event_desc' => $this->eventDescription,
            'change_content' => $this->changeContent,
        ];
    }

    /**
     * 获取数据库管理器
     * @return \Doctrine\DBAL\Schema\MySqlSchemaManager
     */
    private function getSchemaManager()
    {
        if (is_null($this->schema)) {
            /**
             * @var Schema $schema
             */
            $schema = Schema::getConnection();
            //初始化数据库管理器
            $this->schema = $schema->getDoctrineSchemaManager();
            return $this->schema;
        }
        return $this->schema;
    }

    /**
     * 有缓存地获取表结构详情
     * @param $fullTable
     * @return Table
     */
    private function getTableDetailWithCache($fullTable)
    {
        if (Cache::has(self::CACHE_KEY.'table_detail:'.$fullTable))
            return Cache::get(self::CACHE_KEY.'table_detail:'.$fullTable);
        $detail = $this->schema->listTableDetails($fullTable);
        Cache::add(self::CACHE_KEY.'table_detail:'.$fullTable, $detail, self::CACHE_TIME);
        return $detail;
    }

    private function getModelShortTagMapping()
    {
        $config = config('operation_logger');
        $registerClasses = $config['register_class'];
        $mapping = [];
        foreach ( $registerClasses as $registerClass) {
            $mapping[$registerClass['class_name']] = $registerClass['short_tag'];
        }
        return $mapping;
    }

    private function getTableNameFromDbal(Table $tableDetail)
    {
        $operationObjectName = $tableDetail->getComment();
        $nameArr = explode(' ', $operationObjectName);
        return reset($nameArr);
    }
}