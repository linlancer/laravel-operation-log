<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/6/30
 * Time: 21:01
 */

namespace LinLancer\Laravel\RPCService;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

trait BaseRpcTrait
{
    public function rpcSet(string $name, string $condition, array $values): bool
    {
        $name = 'rpcSet'.Str::ucfirst($name);
        if (method_exists($this, $name)) {
            try {
                $resp = call_user_func_array([$this, $name], [$condition, $values]);
            } catch (\Exception $e) {
                return false;
            }

            return boolval($resp);
        }

        return false;
    }

    public function rpcGet(string $name, string $condition): Collection
    {
        $name = 'rpc'.Str::ucfirst($name);
        if (method_exists($this, $name)) {
            try {
                $resp = call_user_func_array([$this, $name], [$condition]);
            } catch (\Exception $e) {
                return new Collection([]);
            }

            $results = [];
            foreach ($resp as $item) {
                $model = new $this;
                $model->attributes = $item;
                $results[] = $model;
            }
            return new Collection($results);
        }

        return new Collection([]);
    }

    public function rpcGetByPage(string $name, string $condition): LengthAwarePaginator
    {
        $name = 'rpc'.Str::ucfirst($name);
        $name .= 'ByPage';
        if (method_exists($this, $name)) {
            $resp = call_user_func_array([$this, $name], [$condition]);
            $results = [];
            foreach ($resp['data'] as $item) {
                $model = new $this;
                $model->attributes = $item;
                $results[] = $model;
            }
            $total = $resp['total'] ?? 0;
            $perPage = $resp['per_page'] ?? config('pagesize', 20);
            $currentPage = $resp['current_page'] ?? 1;
            return new LengthAwarePaginator($results, $total, $perPage, $currentPage);
        }
        return new LengthAwarePaginator([], 0, config('pagesize', 20), 1);
    }
}