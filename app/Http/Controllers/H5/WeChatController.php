<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/9/18
 * Time: 4:37 PM
 */

namespace App\Http\Controllers\H5;

use App\Helpers\WeChatHelper;
use EasyWeChat\Factory as WeChatFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Helpers\UtilHelper;
use App\Model\WechatFans;
use App\Repositories\WechatFansRepository;

class WeChatController extends Controller
{
    private $fans;

    protected $openId, $wxUid, $uid;
    protected $userInfo, $wxUserInfo, $program;

    public function __construct(WechatFansRepository $fans)
    {
        //parent::__construct();
        $this->fans = $fans;
    }

    public function oauthCallback(Request $request)
    {
        $previous_url = $request->session()->previousUrl();

        if ($previous_url == $request->fullUrl()) {
            $previous_url = '/h5/home/index';
        }

        $request->session()->setPreviousUrl($previous_url);

        $request->session()->save();


        $config = config('wechat.official_account.default');
        $this->program = WeChatFactory::officialAccount($config);

        try {

            // 获取 OAuth 授权结果用户信息
            $user = $this->program->oauth->user();
            $wxUserData = $user->getOriginal();
            if ($user) {
                $wxUserInfo = $this->fans->getWeChatUserByOpenId($wxUserData['openid']);
                if (empty($wxUserInfo)) {
                    $uid = 0;
                    if (isset($wxUserData['unionid']) && ($wxUserData['unionid'])) {
                        $mini_program_user_info = $this->fans->getWeChatUserByUnionId($wxUserData['unionid'], WechatFans::TYPE_IS_MINI_PROGRAM);
                        if ($mini_program_user_info) {
                            $uid = $mini_program_user_info->uid;
                        }
                    }
                    $userData = [
                        'uid' => $uid,
                        'type' => WechatFans::TYPE_IS_OFFICIAL,
                        'unionid' => isset($wxUserData['unionid']) ? $wxUserData['unionid'] : '',
                        'openid' => $wxUserData['openid'],
                        'nickname' => WeChatHelper::userTextEncode($wxUserData['nickname']),
                    //    'nickname' => env('APP_NAME') . UtilHelper::createRandomStr(6),
                        'headimgurl' => $wxUserData['headimgurl'],
                        'sex' => $wxUserData['sex'],
                        'country' => $wxUserData['country'],
                        'province' => $wxUserData['province'],
                        'city' => $wxUserData['city'],
                        'session' => '',
                        'session3rd' => ''
                    ];
                    $wxUserInfo = $this->fans->addWeChatUser($userData);
                }else{
                    $userData = [
                        'nickname' => WeChatHelper::userTextEncode($wxUserData['nickname']),
                        'headimgurl' => $wxUserData['headimgurl']
                    ];
                    $res = $this->fans->updateWeChatUser($wxUserInfo->fans_id,$userData);
                }

                $this->wxUserInfo = $wxUserInfo;

                $request->session()->put('wxUserInfo', $this->wxUserInfo);
                $request->session()->save();

                //header('location:' . $targetUrl); // 跳转到 user/profile

                return redirect()->to($previous_url);
            } else {
                return response('用户授权获取失败');
            }

        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());

            \Log::error($exception->getFile());

            \Log::error($exception->getLine());

            \Log::error($exception->getTraceAsString());


            $response = $this->program->oauth->scopes(['snsapi_userinfo'])->setRequest($request)->redirect();

            return $response;
        }
    }

}
