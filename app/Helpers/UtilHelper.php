<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2017/8/8
 * Time: 下午11:22
 */

namespace App\Helpers;

use File;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

class UtilHelper
{

    //系统平台分类
    const SYS_IS_PLATFORM = 1;
    const SYS_IS_COMPANY = 2;
    const SYS_IS_SHOP = 3;

    public static function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }

    public static function formatNumber($no)
    {
        return str_pad($no, 4, '0', STR_PAD_LEFT);
    }

    public static function hideTel($phone)
    {
        $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); //固定电话
        if ($IsWhat == 1) {
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1****$2', $phone);
        } else {
            return preg_replace('/(1[34578]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
        }
    }

    public static function hideIdCard($idcard)
    {
        $idcard = trim($idcard);
        if (strlen($idcard) == 15) {
            return substr_replace($idcard, "****", 8, 4);
        } else if (strlen($idcard) == 18) {
            return substr_replace($idcard, "****", 10, 4);
        } else if (strlen($idcard) > 4) {
            return substr_replace($idcard, "****", strlen($idcard) - 4, 4);
        } else {
            return '*';
        }
    }

    public static function formatMoney($num)
    {
        return sprintf('%.2f', $num);
    }

    public static function getOrderNumber($len = 4, $format = 'YmdHis')
    {
        return date($format) . str_pad(rand(1, 10 * $len - 1), $len, '0', STR_PAD_LEFT);
    }

    public static function getShopOrderNumber($prefix = '', $len = 4, $format = 'ymd')
    {
        return $prefix . date($format) . str_pad(rand(1, 10 * $len - 1), $len, '0', STR_PAD_LEFT);
    }

    public static function getOrderOutTradeNo($orderNum, $logId)
    {
        return 'OD-' . $orderNum . '-' . str_pad($logId, 10, '0', STR_PAD_LEFT);
    }

    public static function getCustomerRechargeOutTradeNo($customerId, $logId)
    {
        return 'CZ-' . str_pad($customerId, 10, '0', STR_PAD_LEFT) . '-' .
            str_pad($logId, 10, '0', STR_PAD_LEFT);
    }

    /**
     * 求两个经纬度之间的距离
     *
     * 赤道半径 6378.137Km ；两极半径 6359.752Km；平均半径 6371.012Km ；赤道周长 40075.7Km.
     *
     * @param float $aLng 地址A的经度
     * @param float $aLat 地址A的纬度
     * @param float $bLng 地址B的经度
     * @param float $bLat 地址B的纬度
     *
     * @return float|int 单位米
     */
    public static function getDistance($aLng, $aLat, $bLng, $bLat)
    {
        //将角度转为狐度
        $aLng = deg2rad($aLng);
        $aLat = deg2rad($aLat);
        $bLng = deg2rad($bLng);
        $bLat = deg2rad($bLat);
        $lngDiff = $aLng - $bLng;
        $latDiff = $aLat - $bLat;
        $diff = 2 * asin(sqrt(pow(sin($latDiff / 2), 2) + cos($aLat) * cos($bLat) * pow(sin($lngDiff / 2), 2))) * 6378.137 * 1000;
        return $diff;
    }

    /**
     * 计算某个经纬度的周围某段距离的正方形的四个点
     *
     * @param
     *            radius 地球半径 平均6378.137km
     * @param
     *            lng float 经度
     * @param
     *            lat float 纬度
     * @param
     *            distance float 该点所在圆的半径，该圆与此正方形内切，默认值为500米
     * @return array 正方形的四个点的经纬度坐标
     */
    public static function getSquarePoint($lng, $lat, $distance = 500, $radius = 6378.137)
    {
        $dlng = 2 * asin(sin($distance / 1000 / (2 * $radius)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);

        $dlat = $distance / $radius;
        $dlat = rad2deg($dlat);

        return array(
            'left-top' => array(
                'lat' => $lat + $dlat,
                'lng' => $lng - $dlng
            ),
            'right-top' => array(
                'lat' => $lat + $dlat,
                'lng' => $lng + $dlng
            ),
            'left-bottom' => array(
                'lat' => $lat - $dlat,
                'lng' => $lng - $dlng
            ),
            'right-bottom' => array(
                'lat' => $lat - $dlat,
                'lng' => $lng + $dlng
            )
        );
    }

    public static function getOrderRewardNumber($orderId)
    {
        return 'DS' . str_pad($orderId, 10, '0', STR_PAD_LEFT);
    }

    public static function getShopCustomerRechargeNumber($shopId)
    {
        return 'CZ' . date('YmdHis') . str_pad($shopId, 10, '0', STR_PAD_LEFT) . rand(1000, 9999);
    }

    public static function createRandomStr($length)
    {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        $strlen = 62;
        while ($length > $strlen) {
            $str .= $str;
            $strlen += 62;
        }
        $str = str_shuffle($str);
        return substr($str, 0, $length);
    }
}
