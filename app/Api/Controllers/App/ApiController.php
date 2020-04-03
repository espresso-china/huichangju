<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-02-22
 * Time: 13:56
 */

namespace App\Api\Controllers\App;

use EasyWeChat\Factory as WeChatFactory;
use Illuminate\Routing\Controller as BaseController;

class ApiController extends BaseController
{
    const REQUEST_SOURCE_IS_MINI = 'mini';
    const REQUEST_SOURCE_IS_H5 = 'h5';
    const REQUEST_SOURCE_IS_WECHAT = 'wechat';
    const REQUEST_SOURCE_IS_IOS = 'ios';
    const REQUEST_SOURCE_IS_ANDROID = 'android';

    protected $session3rd, $openId, $wxUid, $uid, $staffId;
    protected $userInfo, $wxUserInfo, $program;
    protected $request_source;

    public function __construct()
    {
        $this->session3rd = request('session3rd');
        $this->uid = request('uid', 0);
        $this->wxUid = request('wxid', 0);
        //$this->staffId = request('staffId', 0);

        $this->request_source = request('request_source', self::REQUEST_SOURCE_IS_MINI);

//        if (strpos(request()->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false) {
//            $this->request_source = self::REQUEST_SOURCE_IS_WECHAT;
//        }

        //\Log::info('request_source:' . $this->request_source);

        if ($this->request_source == self::REQUEST_SOURCE_IS_MINI) {
            //小程序
            $config = config('wechat.mini_program.default');
            $this->program = WeChatFactory::miniProgram($config);
        } elseif ($this->request_source == self::REQUEST_SOURCE_IS_H5) {
            //H5
            //TODO 待实现
        } elseif ($this->request_source == self::REQUEST_SOURCE_IS_WECHAT) {
            //微信公众号
            $config = config('wechat.official_account.default');
            $this->program = WeChatFactory::officialAccount($config);
        } else {
            //原生APP
        }
    }

}
