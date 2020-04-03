<?php

namespace App\Http\Middleware;

use App\Model\User;
use Auth, Closure, URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopUserAuthenticated
{

    /**
     * 排除权限检查的路由地址
     * @var array
     */
    private $exclued_routes = [
        'admin.home',
        'admin.shop.qrcode',
        'admin.goods.qrcode',
        //'admin.admin_user.editprofile'  //编辑个人用户信息
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     * @throws \Throwable
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (!Auth::guard('admin')->user()) {
            return response()->json(['code' => 404, 'msg' => '用户不存在'], 200);
        }

        $uri = \Dingo\Api\Facade\Route::currentRouteName();

        if (env('APP_ENV') == 'local' || in_array($uri, $this->exclued_routes)) {
            return $next($request);
        }

        $user = Auth::guard('admin')->user();
        if ($user->type != User::TYPE_IS_SHOP || $user->shop_id == 0) {
            return response()->json(['code' => 403, 'msg' => '您没有权限访问商家后台', 'data' => $uri], 200);
        }

        return $next($request);
    }
}
