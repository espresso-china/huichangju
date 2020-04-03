<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/9/19
 * Time: 3:18 PM
 */

namespace App\Http\Controllers\H5\Customer;

use Illuminate\Http\Request;
use App\Helpers\ResultHelper;
use App\Http\Controllers\H5\BaseController as Controller;

class BaseController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware(function (Request $request, $next) {
            $this->wxUserInfo = $request->session()->get('wxUserInfo');
            $this->uid = $this->wxUserInfo ? $this->wxUserInfo->uid : 0;


            if (empty($this->uid)) {
                $request->session()->setPreviousUrl($request->fullUrl());

                $request->session()->save();

                if ($request->isXmlHttpRequest()) {
                    return ResultHelper::json_jump('未注册用户', '/h5/login');
                } else {
                    return redirect('/h5/login');
                }
            }

            return $next($request);
        });
    }
}
