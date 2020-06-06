<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/6/6
 * Time: 10:00
 */

namespace LinLancer\Laravel;

use Illuminate\Database\Eloquent\Collection;

interface ModelFormArray
{
    public function rpcGet(string $name, array $condition): Collection;
}