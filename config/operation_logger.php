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
    //记录日志的表名
    'log_table_name' => 'operation_logger',
    //记录日志所用的数据库连接名
    'log_connection' => 'mysql',
    /**
     * 注册开启日志记录的模型类
     */
    'register_class' => [
        [
            //类名
            'class_name' => \App\Models\Purchase\Requisition::class,
            //类名短标记  将体现在日志中
            'short_tag' => '采购申请单',
            //设置了此项将开启日志短路由
            'short_tag_en' => 'req',

            'related_with' => [
                [
                    //类名
                    'class_name' => \App\Models\Purchase\RequisitionSkuInfo::class,
                    //类名短标记  将体现在日志中
                    'short_tag' => '商品详情',
                ]
            ]
        ],
    ],
];