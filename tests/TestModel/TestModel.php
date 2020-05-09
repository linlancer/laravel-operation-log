<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/9
 * Time: 15:42
 */

namespace LinLancer\Laravel\Tests\TestModel;


use LinLancer\Laravel\EloquentModel;
use LinLancer\Laravel\OperationLogger;

class TestModel extends EloquentModel implements OperationLogger
{

    public $connection = 'default';

    public $table = 'test';

    public $fillable = [
        'name',
        'sex',
        'content',
    ];

    public $timestamps = false;

    public function getCurrentUserId(): int
    {
        return rand(1000,9999);
    }

}