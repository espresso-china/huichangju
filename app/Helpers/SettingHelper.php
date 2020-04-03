<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/2/27
 * Time: 4:45 PM
 */

namespace App\Helpers;

use Cache;
use App\Model\Config;

class SettingHelper
{

    public static function getConfigValue($key, $default = '', $instance_id = 0)
    {
        $item = Config::where('key', $key)->where('instance_id', $instance_id)->first();
        if ($item) {
            return $item->value;
        }
        return $default;
    }

    public static function setConfigValue($key, $val, $instance_id = 0)
    {
        $item = Config::where('key', $key)->where('instance_id', $instance_id)->first();
        if ($item) {
            $item->value = $val;
            return $item->save();
        } else {
            return Config::insertGetId(['key' => $key, 'instance_id' => $instance_id, 'value' => $val]);
        }
    }

}
