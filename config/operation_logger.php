<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 11:46
 */
return [
    //日志短路由的前缀
    'route_prefix' => '',
    //是否开启短路由
    'short_route' => true,
    //记录日志的表名
    'log_table_name' => 'operation_logger',
    //记录日志所用的数据库连接名
    'log_connection' => 'mysql',
    //操作记录返回的排序值
    'order_by' => 'asc',
    //关联的用户表
    'related_user_model' => [
        'class_name' => \App\Models\Erp\Models\ErpMemberModel::class,
        'foreign_key' => 'user_id',
        'owner_key' => 'id',
    ],
    //用户表中所要读取的字段  ownerkey必填入
    'select_field_from_user_model' => [
        'id',
        'member_name',
        'uuid',
        'job_id',
    ],
    //注册开启日志记录的模型类
    'register_class' => [
        [
            //类名
            'class_name' => \App\Models\Purchase\Requisition::class,
            //类名短标记  将体现在日志中
            'short_tag' => '采购申请单',
            //设置了此项将开启日志短路由
            'short_tag_en' => 'req',
            //设置此项  将会在操作日志中展示此字段 用于标识、区别每个操作记录
            'tagged_field' => 'requisition_code',
            //关联本地表键名
            'related_key' => 'requisition_code',
            //关联其他模型
            'related_with' => [
                [
                    //类名
                    'class_name' => \App\Models\Purchase\RequisitionSkuInfo::class,
                    //类名短标记  将体现在日志中
                    'short_tag' => '商品详情',
                    //设置此项  将会在操作日志中展示此字段 用于标识、区别每个操作记录
                    'tagged_field' => 'sku',
                    //关联子表的外键键名
                    'related_key' => 'requisition_code',
                ]
            ]
        ],
    ],
    //不记录变更的字段
    'ignore_list' => [
        '_id',
        '_time',
        'id',
    ],
];