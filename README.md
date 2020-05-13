# laravel-operation-log
A simple logger  for  Eloquent Model to record changelog or user operation log 

---

# Introduction
只适用于PHP >= 7.1  laravel 5.8.* 框架 （其他版本如果需要请自行验证）
* 组件主要实现了数据操作记录与业务代码的解耦、实现自动化、配置化模式的数据库操作自动记录
* 根据数据库表的注释 来完善数据库操作记录的可读性 
* 数据操作记录可以通过关联来记录同一类型下的操作或变更记录
* 可以通过配置快速生成日志的短路由模式 无须再修改路由配置
* 可自定义配置中间件来对返回结果进行二次处理
* 通过模型的事件触发 并且对Eloquent Builder下触发的数据库操作也支持触发
* 当前实现了已更新  已创建 已删除 已强制删除 四个事件的订阅
* 支持动态配置用户信息关联
# Installtion
* 引入包
```
  composer require linlancer/laravel-operation-log
```
* 基类模型需继承自 LinLancer\Laravel\EloquentModel; 并且实现 getCurrentUserId方法 用于识别当前用户
# Usage
配置如下图
```
[
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
```
** 如果没有优化可读性的话 想像一下日志中记录的是 
```
 status由 3 变成 5
```
那这个日志就没有存在的意义了
系统中关于字段名和枚举值的可读性转换做了优化相关规则如下：
字段名称和枚举值的映射采用的是如下的优先级序：
1  读取模型中已有的getSomeAttribute方法所返回的值
2  读取模型中formatSomeAttribute方法所返回的值（如果前期没有用到getSomeAttribute的方法 此时加入会导致返回结果产生变化，可能会对业务产生影响 所以如果不想影响现有业务 也可以使用 formatSomeAttribute方法来替代）
3  如果都没有设置  则会按照推荐的注释标准 去表中读取对应的枚举值

# Standard of annotation
数据库表名  读取空格前的所有字符作为操作对象名  如果设置了 short_tag则取用short_tag
字段名 字段名的读法是按照读取第一个冒号（中英文模式都支持）、或空格前的字符作为字段名
枚举值 枚举值一般采用 值-描述分隔符的形式 分隔符支持 中英文模式下的逗号和分号 （需要注意的是 需要同一种语言模式下的同一种分隔符才能生效）
示例如下
```
CREATE TABLE `hp_purchase_account` (
  `create_user` varchar(32) NOT NULL DEFAULT '' COMMENT '创建者',//字段名为 创建者
  `create_user_id` varchar(32) NOT NULL DEFAULT '' COMMENT '创建者:uuid',//字段名为创建者  枚举值为空
  `auth_status` int(4) NOT NULL DEFAULT '1' COMMENT '授权状态 1-已授权； 2 -未授权, 3-授权过期',//字段名为授权状态 枚举值不合法
  `status` int(4) NOT NULL DEFAULT '1' COMMENT '用户状态:1- 可用; 2-禁用',//字段名为用户状态 枚举值合法
) ENGINE=InnoDB AUTO_INCREMENT=652 DEFAULT CHARSET=utf8 COMMENT='阿里巴 巴采购 账号 列 表';//表名/操作对象为阿里巴 
```
# Others

