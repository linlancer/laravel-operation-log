<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/6/6
 * Time: 10:00
 */

namespace LinLancer\Laravel;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModelFormArray
{
    public function rpcGet(string $name, string $condition): Collection;

    public function rpcGetByPage(string $name, string $condition): LengthAwarePaginator;
}