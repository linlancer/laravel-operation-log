<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 14:03
 */

namespace LinLancer\Laravel\OperationLog\Models;

use LinLancer\Laravel\EloquentModel;

class OperationLogModel extends EloquentModel
{
    public $table = 'operation_log';

    public $fillable = [
        'trigger_class',
        'associated_id',
        'user_id',
        'client_ip',
        'trigger_time',
        'event_desc',
        'change_content',
    ];

    public $timestamps = false;


}