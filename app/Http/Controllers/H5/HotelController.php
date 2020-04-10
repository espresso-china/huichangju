<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/9/23
 * Time: 10:45 AM
 */

namespace App\Http\Controllers\H5;

use App\Helpers\ResultHelper;
use App\Repositories\HotelRepository;
use App\Repositories\MemberRepository;
use App\Repositories\WechatFansRepository;
use Illuminate\Http\Request;
use App\Helpers\UrlHelper;
use App\Model\Hotel;
use App\Repositories\AttachmentRepository;
use mysql_xdevapi\Result;
use Carbon\Carbon;
use Session;
use EasyWeChat\Factory as WeChatFactory;

class HotelController extends BaseController
{
    private $hotel, $attachment, $member, $fans;

    public function __construct(HotelRepository $hotel, AttachmentRepository $attachment, MemberRepository $member, WechatFansRepository $fans)
    {
        parent::__construct();
        $this->hotel = $hotel;
        $this->member = $member;
        $this->fans = $fans;
    }

    public function index(){
        $citys = $this->hotel->getDistrictList(183);
        $this->view_data['citys'] = $citys;
        return view('front.hotel.index', $this->view_data);
    }

    public function lists(Request $request){
        $page = $request->get('page') - 1;
        if ($page < 0) $page = 0;
        $pageSize = $request->get('limit');

        $name = $request->input('name');

        $whereData = [
            'name' => $name
        ];

        $where = $this->hotel->getHotelWhere($whereData);

        $data = $this->hotel->getHotelList($page, $pageSize, $where);
        $this->view_data['lists'] = $data;
        return view('front.hotel.list', $this->view_data);
    }

    public function info(Request $request, $id)
    {
        if ($this->request_resource != 'wechat') {
            $url = route('h5.hotel.info', ['id' => $id]);
            $img = \QrCode::format('png')->margin(5)->size(200)->generate($url);
            $img = 'data:image/png;base64, ' . base64_encode($img);
            return '<div style="width:100%;text-align: center"><img src="' . $img . '" width="200" height="200" style="margin:0 auto;"></div><div style="width:100%;font-size:30px;text-align: center;padding: 10px 0;">请在微信中打开链接</div>';
        }

        $config = config('wechat.official_account.default');
        $app = WeChatFactory::officialAccount($config);
        $this->view_data['js'] = $app->jssdk->buildConfig(array('onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo'), false);

        $goods_info = $this->hotel->getInfo($id);
        if (!$goods_info) {
            $this->view_data['msg'] = '商品不存在';
            return view('front.hotel.msg', $this->view_data);
        }
        $goods_info->increment('clicks');



        return view('front.hotel.show', $this->view_data);
    }


}
