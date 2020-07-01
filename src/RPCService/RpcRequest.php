<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/6/30
 * Time: 20:55
 */

namespace LinLancer\Laravel\RPCService;


use Illuminate\Contracts\Support\Arrayable;

class RpcRequest implements Arrayable
{
    private $req = [
        'table' => '',
        'fields' => '',
        'condition' => '',
        'page' => '',
        'page_size' => '',
    ];

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        // TODO: Implement toArray() method.
    }
}