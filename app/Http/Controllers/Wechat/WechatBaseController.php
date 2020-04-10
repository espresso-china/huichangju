<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2017/8/6 0006
 * Time: 下午 16:48
 */


namespace App\Http\Controllers\WeChat;

use App\Http\Controllers\Controller;

abstract class WechatBaseController extends Controller
{

    /**
     * 视图数据
     * @var array
     */
    var $view_data = [];

    var $open_id;
    var $userInfo;
    var $wxUserInfo;

    function __construct()
    {
    }

    protected function getUserInfo()
    {
        return request()->session()->get('current_user');
    }

    protected function getOpenId()
    {
        return request()->session()->get('openid');
    }

    protected function getWeChatUserInfo()
    {
        return request()->session()->get('wechat_user');
    }

}