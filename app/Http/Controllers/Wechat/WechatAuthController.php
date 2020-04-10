<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2017/7/31 0031
 * Time: 下午 19:42
 */

namespace App\Http\Controllers\WeChat;


use App\Helpers\SmsHelper;
use App\Helpers\WeChatHelper;
use App\Http\Controllers\Controller;
use App\Repositories\WechatFansRepository;
use EasyWeChat\Foundation\Application;
use Session, Log;

/**
 * 微信服务通讯.
 */
class WechatAuthController extends Controller
{

    private $config;
    private $fans;

    public function __construct(WechatFansRepository $fans)
    {
        $this->fans = $fans;
        $this->config = WeChatHelper::getConfigs();
    }

    /**
     *
     */
    public function login($type = '1')
    {
        $openid = session('openid');
        if (empty($openid)) {
            $app = new Application($this->config);
            $oauth = $app->oauth;
            return $oauth->scopes(['snsapi_userinfo'])->redirect();
        }
        $targetUrl = session('target_url');
        $targetUrl = $targetUrl ?: route('front.wechat.member.index');
        return redirect($targetUrl);
    }

    public function callback()
    {
        $app = new Application($this->config);

        // 获取 OAuth 授权结果用户信息
        $oauth = $app->oauth;
        $user = $oauth->user();
        $openid = $user->getId();
        $avatar = $user->getAvatar();
        $nickname = $user->getName();

        if (empty($user) || empty($openid)) {
            return response('请在微信客户端中访问此链接。');
        }

        $data = [
            'openid' => $openid,
            'avatar' => $avatar,
            'nickname' => WeChatHelper::userTextEncode($nickname)
        ];

        $uid = $this->fans->updateMember($data);
        session(['wechat_user' => $user, 'openid' => $openid, 'uid' => $uid]);

        $targetUrl = session('target_url');
        $targetUrl = $targetUrl ?: route('front.wechat.member.index');
        return redirect($targetUrl);
    }

}

?>