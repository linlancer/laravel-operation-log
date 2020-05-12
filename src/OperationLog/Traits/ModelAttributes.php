<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/12
 * Time: 11:11
 */

namespace LinLancer\Laravel\OperationLog\Traits;


trait ModelAttributes
{
    public function setOriginal(array $attributes):void
    {
        foreach ($attributes as $key => $value) {
            $this->original[$key] = $value;
        }
    }
}