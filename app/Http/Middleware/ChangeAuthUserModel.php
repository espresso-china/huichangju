<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ChangeAuthUserModel
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        config(['jwt.user' => '\App\Model\User']);    //重要用于指定特定model
        config(['auth.providers.users.model' => \App\Model\User::class]);//重要用于指定特定model！！！！
        return $next($request);
    }
}