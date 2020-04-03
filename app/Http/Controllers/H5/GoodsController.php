<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/9/23
 * Time: 10:45 AM
 */

namespace App\Http\Controllers\H5;

use App\Helpers\ResultHelper;
use App\Repositories\MemberRepository;
use App\Repositories\WechatFansRepository;
use Illuminate\Http\Request;
use App\Helpers\UrlHelper;
use App\Model\Goods;
use App\Repositories\PromotionRepository;
use App\Repositories\AttachmentRepository;
use App\Repositories\GoodsRepository;
use App\Repositories\ShopRepository;
use App\Repositories\FxPromoterRepository;
use App\Repositories\FxCommissionRepository;
use App\Repositories\OrderRepository;
use mysql_xdevapi\Result;
use Carbon\Carbon;
use EasyWeChat\Factory as WeChatFactory;

class GoodsController extends BaseController
{
    private $goods, $attachment, $promotion, $shop, $promoter, $commission, $order, $member, $fans;

    public function __construct(GoodsRepository $goods, AttachmentRepository $attachment,
                                PromotionRepository $promotion, ShopRepository $shop,
                                FxPromoterRepository $promoter, FxCommissionRepository $commission,
                                OrderRepository $order, MemberRepository $member, WechatFansRepository $fans)
    {
        parent::__construct();
        $this->goods = $goods;
        $this->attachment = $attachment;
        $this->promotion = $promotion;
        $this->shop = $shop;
        $this->promoter = $promoter;
        $this->commission = $commission;
        $this->order = $order;
        $this->member = $member;
        $this->fans = $fans;
    }

    public function info(Request $request, $id)
    {
        if ($this->request_resource != 'wechat') {
            $url = route('h5.goods.info', ['id' => $id]);
            $img = \QrCode::format('png')->margin(5)->size(200)->generate($url);
            $img = 'data:image/png;base64, ' . base64_encode($img);
            return '<div style="width:100%;text-align: center"><img src="' . $img . '" width="200" height="200" style="margin:0 auto;"></div><div style="width:100%;font-size:30px;text-align: center;padding: 10px 0;">请在微信中打开链接</div>';
        }

        $config = config('wechat.official_account.default');
        $app = WeChatFactory::officialAccount($config);
        $this->view_data['js'] = $app->jssdk->buildConfig(array('onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo'), false);

        $goods_info = $this->goods->getGoodsInfo($id);
        if (!$goods_info) {
            $this->view_data['msg'] = '商品不存在';
            return view('front.goods.msg', $this->view_data);
        }
        $goods_info->increment('clicks');

        $clickData = [
            'wx_uid' => $this->wxUserInfo->fans_id,
            'goods_id' => $id,
            'ip' => $request->ip()
        ];

        $this->member->saveMemberClickLogs($clickData);

        $this->view_data['uid'] = $this->uid;
        $this->view_data['skuData'] = $this->goods->getGoodsSkusByGoodsId($id);
        $this->view_data['SpecData'] = $goods_info->goods_spec_format ? json_decode($goods_info->goods_spec_format, true) : [];
        $this->view_data['memberInfo'] = [];

        if ($this->uid > 0) {
            $memberInfo = $this->member->getInfoByWxId($this->wxUserInfo->fans_id);
            if ($memberInfo) {
                $this->view_data['memberInfo'] = $memberInfo;
            }
        }

        $this->view_data['goods_info'] = $goods_info;

        $this->view_data['goods_cover'] = UrlHelper::getGoodsCover($goods_info->goods_cover);

        $images = [];
        if ($goods_info->img_id_array) {
            $img_ids = explode(",", $goods_info->img_id_array);
            foreach ($img_ids as $key => $v) {
                $img = $this->attachment->find($v);
                $images[$key]['url'] = $img ? asset($img->qiniu_url) : '';
            }
        }
        $this->view_data['goods_images'] = $images;
        //访客列表
        $this->view_data['pin_peoples'] = $this->member->getMemberClickLogs($goods_info->goods_id);
//
        $shop_info = $this->shop->getShop($goods_info->shop_id);
//        $shop_info->shop_logo = UrlHelper::getShopCover($shop_info->shop_logo);

        $this->view_data['phone'] = $shop_info->shop_phone;
        //分销商品获取分销佣金记录
        $rate_info = $this->promoter->getGoodsInfo($goods_info->goods_id, $goods_info->shop_id);
        if ($rate_info && $rate_info->is_open) {

            $commissions = $this->commission->getGoodsCommissionOrderByGoodsId($goods_info->goods_id);

            $this->view_data['commissions'] = $commissions;
        } else {
            $this->view_data['commissions'] = [];
        }
        if ($goods_info->promotion_type == Goods::PROMOTION_TYPE_IS_PINTUAN) {
            return $this->pintuan_goods_info($goods_info);
        }

        return $this->pintuan_goods_info($goods_info);
    }

    public function danmu($goods_id)
    {
        $danmu = [];
        $i = 1;
        $goods_info = $this->goods->getGoods($goods_id);
        $commissions = $this->commission->getGoodsCommissionsByGoodsId($goods_info->goods_id, 10);
        foreach ($commissions as $commission) {
            $danmu[$i] = [
                //'type' => 'commission',
                // 'info' => $commission->member->member_name,
                'img' => $commission->member->avatar,
                'close' => false,
                'info' => $commission->member->member_name . '分享链接获得红包' . $commission->commission_money . '元',
            ];
            $i++;
        }

        if ($goods_info->promotion_type == Goods::PROMOTION_TYPE_IS_PINTUAN) {
            $pintuan_orders = $this->order->getOrderPintuanByGoodsId($goods_info->goods_id, 10);

            foreach ($pintuan_orders as $order) {
                $danmu[$i] = [
                    //       'type' => 'order',
                    //  'username' => $order->member->member_name,
                    'img' => $order->member->avatar,
                    'close' => false,
                    'info' => $order->group->uid == $order->uid ? $order->member->member_name . '开团成功 ' : $order->member->member_name . '拼团成功 '
                ];
                $i++;
            }
        }

        //      ksort($danmu);

        return ResultHelper::json_result('success', $danmu);
    }

    /**
     * 拼团分销商品
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function pintuan_goods_info($goods_info)
    {
        $pin_groups = [];
        $pin_info = [];
        $promotionInfo = $this->promotion->getPinTuanValidGoods($goods_info->promotion_id);
        //\Log::info($promotionInfo);


        if ($promotionInfo) {
            $pin_info = $this->promotion->getPinTuanInfo($promotionInfo->pintuan_id);
            $PinTuanGroups = $this->promotion->getPintuanGroupsValid($goods_info->goods_id, 5);
            $data = [];
            foreach ($PinTuanGroups as $k => $v) {
                $wxInfo = $this->fans->getWeChatUserByUid($v->uid, 2);
                $data[$k]['avatar'] = $wxInfo ? $wxInfo->headimgurl : '';
                $data[$k]['group_id'] = $v->group_id;
                $data[$k]['name'] = $v->user_name;
                $data[$k]['need_people'] = $v->people - $v->has_people;
                $data[$k]['end_time'] = $v->end_time->toDateTimeString();
            }
            $pin_groups = $data;
            $this->view_data['pin_groups'] = $pin_groups;
            //      $pin_info->end_time = $pin_info->end_time->format("m/d/Y H:i:s");
            $this->view_data['end_time'] = Carbon::parse($pin_info->end_time)->Format("m/d/Y H:i:s");
            $this->view_data['pin_info'] = $pin_info;

            $this->view_data['pin_people_total'] = $this->promotion->getPintuanPeopleCountByGoodsId($goods_info->goods_id);

            $this->view_data['pin_group_count'] = $this->promotion->getPintuanGroupCountByGoodsId($goods_info->goods_id);
        } else {
            $this->view_data['msg'] = '非拼团商品';
            return view('front.goods.msg', $this->view_data);
        }

        return view('front.goods.pintuan_info', $this->view_data);
    }

    public function getSkuInfo(Request $request)
    {
        $item_value = $request->input('data', 0);
        $item_value = rtrim($item_value, ";");
        $goods_id = $request->input('goods_id');
        $skuData = $this->goods->getGoodsSkuByGoodsId($goods_id, $item_value);
        if ($skuData) {
            return ResultHelper::json_result('success', $skuData, 200);
        }
        return ResultHelper::json_error('没有规格数据', 201);
    }
}
