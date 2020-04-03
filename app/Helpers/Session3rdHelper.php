<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2018/8/2
 * Time: 上午9:01
 */

namespace App\Helpers;


class Session3rdHelper
{
    public static function save($session, $key, $val, $timeout = 600)
    {
        cache([$session . '_' . $key => $val], $timeout);
    }

    public static function get($session, $key, $default = '')
    {
        return cache($session . '_' . $key, $default);
    }

    public static function save_sms_code($session, $code)
    {
        self::save($session, 'sms_code', $code, 5);
    }

    public static function get_sms_code($session)
    {
        return self::get($session, 'sms_code');
    }
}