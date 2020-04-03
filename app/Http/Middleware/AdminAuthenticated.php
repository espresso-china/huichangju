<?php

namespace App\Http\Middleware;

use Auth, Closure, URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminAuthenticated
{

    /**
     * 排除权限检查的路由地址
     * @var array
     */
    private $exclued_routes = [
        'admin.home',
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
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return response()->json(['code' => 404, 'msg' => '用户不存在'], 200);
        }

        $uri = \Dingo\Api\Facade\Route::currentRouteName();

        if (env('APP_ENV') == 'local' || in_array($uri, $this->exclued_routes) || $user->name == 'admin') {
            return $next($request);
        }

        //$previousUrl = URL::previous();
        //Log::info(\Dingo\Api\Facade\Route::currentRouteName());

        if ($user->cant($uri)) {
            //Log::info(json_encode(Auth::guard('admin')->user()->roles()));
            //Log::info(json_encode(Auth::guard('admin')->user()->cachedRoles()));
            return response()->json(['code' => 403, 'msg' => '您没有权限执行此操作', 'data' => $uri], 200);
        }

        return $next($request);
    }
}
