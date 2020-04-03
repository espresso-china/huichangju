<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2018/4/19
 * Time: 下午4:34
 */

namespace App\Helpers;


class UrlHelper
{
    public static function getUserAvatar($avatar, $default = '/images/nopic.png')
    {
        return $avatar ?: asset($default);
    }

    public static function getShopLogo($logo, $default = '/images/nopic.png')
    {
        return asset($logo ?: $default);
    }

    public static function getShopCover($cover, $default = '/images/nopic.png')
    {
        return asset($cover ?: $default);
    }

    public static function getGoodsCover($cover, $default = '/images/nopic.png')
    {
        return asset($cover ?: $default);
    }

    public static function getMenuIcon($img, $default = '/images/nopic.png')
    {
        return asset($img ?: $default);
    }

    public static function getFocusCover($cover, $default = '/images/nopic.png')
    {
        return asset($cover ?: $default);
    }

    public static function getActivityCover($cover, $default = '/images/nopic.png')
    {
        return asset($cover ?: $default);
    }
    public static function getNewsCover($cover, $default = '/images/nopic.png')
    {
        return asset($cover ?: $default);
    }

    public static function asset($uri)
    {
        return asset($uri);
    }

    public static function upload($uri)
    {
        return '/' . $uri;
    }
}