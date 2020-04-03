<?php

/*
 * This file is part of Commidity
 *
 * (c) Tacker <tacker.cn@gmail.com>
 *
 */

namespace Xndl\YsePay\YsePay\Facades;

use Illuminate\Support\Facades\Facade;
use Xndl\YsePay\Contracts\YsePayInterface;

/**
 * This is the Commodity facade class.
 *
 * @author Tacker <tacker.cn@gmail.com>
 */
class YsePay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    /**
     * 默认为 Server
     *
     * @return string
     */
    static public function getFacadeAccessor()
    {
        return "ysepay";
    }

    /**
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    static public function __callStatic($name, $args)
    {
        $app = static::getFacadeRoot();

        if (method_exists($app, $name)) {
            return call_user_func_array([$app, $name], $args);
        }

        return $app->$name;
    }
}
