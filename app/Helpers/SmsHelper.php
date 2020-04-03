<?php
/**
 * Created by PhpStorm.
 * User: Tacker
 * Date: 17/1/3
 * Time: 13:50
 */

namespace App\Helpers;

use PhpSms;
use App\Exceptions\SmsException;

class SmsHelper
{

    /**
     * 解析手动输入的参数
     * @param $params
     * @return array
     */
    public static function parseInputParams($params)
    {
        $result = [];
        $params = explode("\n", $params);
        foreach ($params as $param) {
            $param = explode(':', $param);
            if (count($param) == 2) {
                $result[$param[0]] = trim($param[1]);
            } else if (count($param) > 2) {
                $key = $param[0];
                $val = array_slice($param, 1);
                $result[$key] = implode(':', $val);
            }
        }
        return $result;
    }

    /**
     * 根据模版发送短信
     * @param $to
     * @param $content
     * @param $templates
     * @param $params
     * @param bool $immediately
     * @return mixed
     */
    public static function sendTpl($to, $content, $params, $templates, $immediately = false)
    {
        return self::_send($to, $content, $templates, $params, $immediately);
    }

    /**
     * 直接发送短信
     * @param string $to 接收人手机号
     * @param $params
     * @param $content
     * @param bool $immediately
     * @return mixed
     */
    public static function sendMsg($to, $content, $immediately = false)
    {
        return self::_send($to, $content, [], [], $immediately);
    }

    protected static function _send($to, $content, $templates, $params, $immediately = false)
    {
        // 短信模版
//        $templates = [
//            'YunTongXun' => $tplInfo->ytx_tpl_id,
//            'YunPian' => $tplInfo->yp_tpl_id,
//            'Qcloud' => $tplInfo->tx_tpl_id,
//            'ChuangLan' => 0,
//            'SmsBao' => 0,
//        ];

        $to = ['86', $to];

        // 短信内容
        \Log::info($content);

        // 只希望使用模板方式发送短信,可以不设置content(如:云通讯、Submail、Ucpaas)
        // PhpSms::make()->to($to)->template($templates)->data($tempData)->send();

        // 只希望使用内容方式放送,可以不设置模板id和模板data(如:云片、luosimao)
        // PhpSms::make()->to($to)->content($content)->send();

        // 同时确保能通过模板和内容方式发送,这样做的好处是,可以兼顾到各种类型服务商
        if ($templates && $params) {
            $sms = PhpSms::make()->to($to)->template($templates)->data($params)->content($content);
        } else {
            $sms = PhpSms::make()->to($to)->content($content);
        }

        $result = $sms->send($immediately);

        \Log::info(var_export($result, true));

        return $result;
    }

    public static function parseParams($tpl, $params, $type)
    {
        foreach ($params as $key => $val) {
            $tpl = ($type == 'yp') ?
                str_replace('#' . $key . '#', trim($val), $tpl) :
                str_replace('{' . $key . '}', trim($val), $tpl);
        }
        return '【' . env('SMS_SIGN_NAME', '幸孕淘淘') . '】' . $tpl;
    }

    public static function getSn()
    {
        return date('YmdHis') . rand(1000, 9999);
    }

    public static function isPhone($number)
    {
        return preg_match('/^1[34578]\d{9}$/', $number);
    }

    public static function length($str)
    {
        $i = 0;
        $count = 0;
        $len = strlen($str);
        while ($i < $len) {
            $chr = ord($str[$i]);
            $count++;
            $i++;
            if ($i >= $len) {
                break;
            }
            if ($chr & 0x80) {
                $chr <<= 1;
                while ($chr & 0x80) {
                    $i++;
                    $chr <<= 1;
                }
            }
        }
        return self::smsCount($count);
    }

    public static function smsCount($num)
    {
        if ($num < 70) {
            return 1;
        } else {
            return ceil($num / 64);
        }
    }
}