<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-02-22
 * Time: 13:56
 */

namespace App\Api\Controllers\Web;

use EasyWeChat\Factory as WeChatFactory;
use Illuminate\Routing\Controller as BaseController;

class ApiController extends BaseController
{
    protected $uid, $staffId;
    protected $userInfo;

    public function __construct()
    {
        $this->uid = request('uid', 0);
        //$this->staffId = request('staffId', 0);
    }

}
