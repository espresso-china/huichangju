<?php

namespace App\Api\Controllers\Admin;

use App\Model\User;
use Auth;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use EasyWeChat\Factory as WeChatFactory;

class BaseController extends Controller
{
    use Helpers;

    protected $auth;
    protected $guard = 'admin';
    protected $shop_id = 0;
    protected $user_info = null;
    protected $uid = 0;

    /****
     * BaseController constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard($this->guard);
        $this->user_info = $this->auth->user();

        if ($this->user_info) {

            $this->uid = $this->user_info->id;

            if ($this->user_info->type == User::TYPE_IS_SHOP) {
                $this->shop_id = $this->user_info->shop_id;
            } else {
                $this->shop_id = 0;
            }

        }
        $config = [
            'app_id' => env('WECHAT_MINI_PROGRAM_APPID', '###'),
            'secret' => env('WECHAT_MINI_PROGRAM_SECRET', '###'),

            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file' => storage_path('/logs/wechat.log'),
            ],
        ];
        $this->program = WeChatFactory::miniProgram($config);

    }
}  