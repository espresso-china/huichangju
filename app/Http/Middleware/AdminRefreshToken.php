<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

// 注意，我们要继承的是 jwt 的 BaseMiddleware
class AdminRefreshToken extends BaseMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed
     * @throws JWTException
     */
    public function handle($request, Closure $next)
    {
        // 检查此次请求中是否带有 token，如果没有则抛出异常。
        $authToken = Auth::guard('admin')->getToken();
        if (!$authToken) {
            return response(['code' => 401, 'msg' => '登录访问令牌信息无效'], 200);
        }

        // 检测用户的登录状态，如果正常则通过
        if (Auth::guard('admin')->check()) {

            try {
                $payload = Auth::guard('admin')->payload();
            } catch (\Exception $exception) {
                return response(['code' => 401, 'msg' => '登录访问令牌信息已过期'], 200);
            }

            $admin_id = $payload['sub'];
            $time = $payload['exp'];

            //刷新Token
            if (($time - time()) < 10 * 60 && ($time - time()) > 0) {

                $token = Auth::guard('admin')->refresh();
                if ($token) {
                    $request->headers->set('Authorization', 'Bearer ' . $token);
                } else {
                    return response(['code' => 401, 'msg' => '登录访问令牌信息已过期，刷新失败'], 200);
                }

                // 在响应头中返回新的 token
                $respone = $next($request);

                if (isset($token) && $token) {
                    $respone->headers->set('Authorization', 'Bearer ' . $token);
                }

                return $respone;
            }

            //token通过验证 执行下一补操作
            return $next($request);
        }

        return response(['code' => 401, 'msg' => '登录访问令牌信息已过期'], 200);
    }
}
