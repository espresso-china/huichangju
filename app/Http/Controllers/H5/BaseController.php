<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/9/19
 * Time: 8:46 AM
 */

namespace App\Http\Controllers\H5;

use EasyWeChat\Factory as WeChatFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{

    protected $view_data = [];
    protected $openId, $wxUid, $wxUserInfo;
    protected $uid, $userInfo, $program;

    protected $request_resource = 'h5';

    public function __construct()
    {
        //注入微信用户信息校验
        if (strpos(request()->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false) {
            $this->request_resource = $this->view_data['source'] = 'wechat';

            $config = config('wechat.official_account.default');
            $this->program = WeChatFactory::officialAccount($config);

            $this->middleware(function (Request $request, $next) {
                $this->wxUserInfo = $request->session()->get('wxUserInfo');

                if (empty($this->wxUserInfo)) {


                    $request->session()->setPreviousUrl($request->fullUrl());

                    $request->session()->save();

                    $response = $this->program->oauth->scopes(['snsapi_userinfo'])
                        ->setRequest($request)->redirect();

                    return $response;
                }

                $this->uid = $this->wxUserInfo->uid;

                return $next($request);
            });
        } else {
            $this->middleware(function (Request $request, $next) {

                $this->uid = $request->session()->get('uid', 0);

                return $next($request);
            });
        }

    }
}
