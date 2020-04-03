<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/9/19
 * Time: 3:56 PM
 */

namespace App\Http\Controllers\H5\Customer;

use App\Helpers\SettingHelper;
use App\Helpers\UrlHelper;
use App\Model\MemberFavorite;
use App\Model\MemberLogs;
use App\Model\Order;
use App\Model\OrderPintuan;
use App\Model\WechatFans;
use App\Repositories\NoticeRepository;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use App\Helpers\UtilHelper;
use App\Helpers\ResultHelper;
use App\Helpers\NoticeHelper;
use App\Repositories\MemberRepository;
use App\Repositories\WechatFansRepository;
use App\Http\Controllers\H5\BaseController;

class MemberController extends BaseController
{
    private $fans, $member, $notice, $order;

    public function __construct(WechatFansRepository $fans, MemberRepository $member, NoticeRepository $notice, OrderRepository $order)
    {
        parent::__construct();
        $this->fans = $fans;
        $this->member = $member;
        $this->notice = $notice;
        $this->order = $order;
    }

    public function registerByPhone(Request $request)
    {

        if ($this->request_resource == 'h5' && $this->uid) {
            return ResultHelper::json_error('用户已登录');
        }

        if ($this->request_resource == 'wechat' && $this->wxUserInfo->uid) {
            return ResultHelper::json_error('用户已登录');
        }

        $phone = $request->get('phone');
        if (empty($phone)) {
            return ResultHelper::json_error('手机号码不能为空');
        }

        $reg_sms_code = $request->session()->get('reg_sms_code');
        $code = $request->input('code');
        if (empty($code) || $reg_sms_code != $code) {
            return ResultHelper::json_error('手机验证码错误');
        }

        $wxUserInfo = $this->wxUserInfo;
        $memberInfo = $this->member->getInfoByPhone($phone);
        if ($memberInfo) {
            $data = [
                'memberInfo' => $memberInfo,
                'uid' => $memberInfo->uid
            ];

            if (empty($wxUserInfo->uid)) {
                if ($wxUserInfo->unionid) {
                    $this->fans->updateUidByUnionId($wxUserInfo->unionid, $memberInfo->uid);
                } else {
                    $this->fans->updateUidByOpenId(WechatFans::TYPE_IS_OFFICIAL, $wxUserInfo->openid, $memberInfo->uid);
                }
            }

            $wxUserInfo->uid = $memberInfo->uid;
            $request->session()->put('uid', $memberInfo->uid);
            $request->session()->put('wxUserInfo', $wxUserInfo);
            $request->session()->save();

            return ResultHelper::json_error('该号码已经绑定', $data, 200);
        }

        if ($this->request_resource == 'h5') {
            $data = [
                'phone' => $phone,
                'member_name' => env('APP_NAME') . UtilHelper::createRandomStr(6),
                'member_level' => 1,
                'member_point' => 0,
                'member_balance' => 0,
                'avatar' => '',
                'wx_uid' => 0
            ];
        } else {
            $data = [
                'phone' => $phone,
                'member_name' => $wxUserInfo->nickname,
                'member_level' => 1,
                'member_point' => 0,
                'member_balance' => 0,
                'avatar' => $wxUserInfo->headimgurl,
                'wx_uid' => $wxUserInfo->fans_id
            ];
        }

        $res = $this->member->create($data);
        if (empty($res)) {
            return ResultHelper::json_error('用户创建失败');
        }

        $wxUserInfo->uid = $res->uid;
        if ($wxUserInfo->unionid) {
            $this->fans->updateUidByUnionId($wxUserInfo->unionid, $res->uid);
        } else {
            $this->fans->updateUidByOpenId(WechatFans::TYPE_IS_OFFICIAL, $wxUserInfo->openid, $res->uid);
        }

        $request->session()->put('uid', $res->uid);
        $request->session()->put('wxUserInfo', $wxUserInfo);
        $request->session()->save();

        $data = [
            'memberInfo' => $res,
            'uid' => $res->uid
        ];

        return $res ? ResultHelper::json_result('注册成功', $data, 200) : ResultHelper::json_error('注册失败');

    }

    public function sendSmsCode(Request $request)
    {
        $phone = $request->input('phone');
        $memberInfo = $this->member->getInfoByPhone($phone);
        if ($memberInfo) {
            $fansInfo = $this->fans->getWeChatUserByUid($memberInfo->uid,2);
            if($fansInfo){
                return ResultHelper::json_error('该号码已经被注册过了');
            }
        }
        $template_code = 'register_validate';
        $code = rand(100000, 999999);
        $notice_id = $this->notice->createSmsCodeNotice($template_code, $phone, $code);

        if ($notice_id['status']) {
            NoticeHelper::send($notice_id);

            $request->session()->put('reg_sms_code', $code);
            $request->session()->save();

            return ResultHelper::json_result('success', null, 200);
        } else {
            return ResultHelper::json_error($notice_id['msg']);
        }
    }

    public function myProfile()
    {
        $uid = $this->uid;
        $data = [];
        $data['goods'] = MemberFavorite::where('uid', $uid)->where('fav_type', 'goods')->count();
        $data['shops'] = MemberFavorite::where('uid', $uid)->where('fav_type', 'shop')->count();
        $data['logs'] = MemberLogs::where('uid', $uid)->count();
        $this->view_data['collects'] = $data;
        $this->view_data['memberInfo'] = $uid > 0 ? $this->member->find($uid) : false;

        $this->view_data['wait_pay'] = $this->order->getOrderCountByBuyerId($uid, Order::ORDER_STATUS_IS_WAIT_PAY);
        $this->view_data['wait_express'] = $this->order->getOrderCountByBuyerId($uid, Order::ORDER_STATUS_IS_WAIT_EXPRESS);
        $this->view_data['expressed'] = $this->order->getOrderCountByBuyerId($uid, Order::ORDER_STATUS_IS_RECEIVED);

        return view('front.member.profile', $this->view_data);
    }

    public function myOrder(Request $request)
    {
        $uid = $this->uid;
        $page = $request->input('page', 0);
        $size = $request->input('size', 5);
        $type = $request->input('type', 0);
        $page--;
        switch ($type) {
            case 1:
                $order_status = Order::ORDER_STATUS_IS_WAIT_PAY;
                break;
            case 2:
                $order_status = Order::ORDER_STATUS_IS_WAIT_EXPRESS;
                break;
            case 3:
                $order_status = Order::ORDER_STATUS_IS_RECEIVED;
                break;
            case 4:
                $order_status = Order::ORDER_STATUS_IS_COMPLETE;
                break;
            case 5:
                $order_status = Order::ORDER_STATUS_IS_CLOSED;
                break;
            default:
                $order_status = null;
                break;
        }
        //\Log::info($type . '-' . $order_status);
        $orders = $this->order->getGoodsOrderByBuyerId($page, $size, $uid, $order_status);
        $orders_data = [];
        foreach ($orders as $order) {
            $order_goods = $this->order->getOrderGoodsByOrderId($order->order_id);
            $goods_data = [];
            foreach ($order_goods as $goods) {

                $picture = UrlHelper::getGoodsCover('');
                if ($goods->picture) {
                    $picture = UrlHelper::getShopCover($goods->picture->qiniu_url);
                }

                $goods_data[] = [
                    'order_goods_id' => $goods->order_goods_id,
                    'goods_id' => $goods->goods_id,
                    'goods_name' => $goods->goods_name,
                    'sku_id' => $goods->sku_id,
                    'sku_name' => $goods->sku_name,
                    'price' => $goods->price,
                    'money' => $goods->goods_money,
                    'num' => $goods->num,
                    'picture' => $picture,
                    'promotion_type' => $goods->promotion_type_id,
                    'promotion_type_name' => $goods->promotion_type_name,
                ];
            }


            $pickup = null;
            if ($order->shipping_type == Order::SHIPPING_TYPE_IS_SELF) {
                $pickup_info = $this->order->getOrderPickupInfoByOrderId($order->order_id);
                if ($pickup_info) {
                    $pickup = [
                        'id' => $pickup_info->id,
                        'name' => $pickup_info->name,
                        'contact' => $pickup_info->contact,
                        'phone' => $pickup_info->phone,
                        'address' => $pickup_info->address,
                        'buyer_name' => $pickup_info->buyer_name,
                        'buyer_mobile' => $pickup_info->buyer_mobile,
                        'longitude' => $pickup_info->longitude,
                        'latitude' => $pickup_info->latitude
                    ];
                }
            }

            $orders_data[] = [
                'order_id' => $order->order_id,
                'order_no' => $order->order_no,
                'out_trade_no' => $order->out_trade_no,
                'shop_id' => $order->shop_id,
                'shop_name' => $order->shop_name,
                'order_status' => $order->order_status,
                'create_time' => $order->create_time->toDateTimeString(),
                'shipping_time' => $order->shipping_time,
                'goods_money' => $order->goods_money,
                'order_money' => $order->order_money,
                'give_point' => $order->give_point,
                'pay_status' => $order->pay_status,
                'shipping_status' => $order->shipping_status,
                'order_status_name' => $order->order_status_name,
                'pay_status_name' => $order->pay_status_name,
                'shipping_status_name' => $order->shipping_status_name,
                'pickup' => $pickup,
                'goods' => $goods_data,
                'actions' => $order->actions['member_operation'],
                'tips' => $this->getOrderTips($order),
            ];

        }

         $this->view_data['orders'] = $orders_data;
        return view('front.member.order', $this->view_data);
    }

    public function rebate()
    {

        return view('front.member.rebate', $this->view_data);
    }

    public function entry()
    {
        return view('front.member.entry', $this->view_data);
    }

    public function entryInfo()
    {
        $this->view_data['status'] = 1;
        return view('front.member.info', $this->view_data);
    }

    public function entryList()
    {
        return view('front.member.list', $this->view_data);
    }

    public function entryRule()
    {
        return view('front.member.rule', $this->view_data);
    }

    public function rules(){
        return view('front.franchisee.rules',$this->view_data);
    }

    public function apply(){
        return view('front.franchisee.apply',$this->view_data);
    }

    public function confirm(){
        return view('front.franchisee.confirm',$this->view_data);
    }

    public function set(){
        $uid = $this->uid;
        $this->view_data['memberInfo'] = $uid > 0 ? $this->member->find($uid) : false;
        return view('front.member.set',$this->view_data);
    }

    public function suggest(){
        return view('front.member.suggest',$this->view_data);
    }

    public function abouts(){
        return view('front.member.abouts',$this->view_data);
    }

    public function activity(){
        return view('front.activity.list',$this->view_data);
    }

    public function activityInfo(){
        return view('front.activity.info',$this->view_data);
    }

    public function activitySignup(){
        return view('front.activity.signup',$this->view_data);
    }

    public function applyIndex(){
        $this->view_data['status'] = 1;
        return view('front.apply.index',$this->view_data);
    }

    public function register(){
        return view('front.member.register',$this->view_data);
    }


    private function getOrderTips($order_info)
    {
        if ($order_info->order_type == Order::ORDER_TYPE_IS_PIN) {
            $pintuan_order_info = $this->order->getOrderPintuanByOrderId($order_info->order_id);
            if ($pintuan_order_info->pin_status == OrderPintuan::STATUS_IS_NORMAL) {
                $pintuan_group_info = $this->promotion->getPintuanGroupInfo($pintuan_order_info->group_id);
                $need_people = $pintuan_group_info->people - $pintuan_group_info->has_people;
                $desc = '待成团，还需 ' . $need_people . ' 人成团，截止：' . $pintuan_group_info->end_time->toDateTimeString();
                return ['title' => '拼团中', 'desc' => $desc];
            }
        }
        switch ($order_info->order_status) {
            case Order::ORDER_STATUS_IS_WAIT_PAY:
                $auto_close_minute = SettingHelper::getConfigValue('ORDER_BUY_CLOSE_TIME', 60);
                $time = Carbon::createFromTimeString($order_info->create_time)->addMinutes($auto_close_minute)->toDateTimeString();
                return ['title' => '等待买家付款', 'desc' => '订单将在 ' . $time . ' 之后自动关闭'];
            case Order::ORDER_STATUS_IS_WAIT_EXPRESS:
                if ($order_info->shipping_type == Order::SHIPPING_TYPE_IS_SELF) {
                    return ['title' => '等待买家提货', 'desc' => '请在 ' . $order_info->shipping_time . ' 之后到提货点提货'];
                } else {
                    return ['title' => '等待卖家发货', 'desc' => '订单将在 ' . $order_info->shipping_time . ' 之前发货'];
                }
            case Order::ORDER_STATUS_IS_EXPRESSED:
                $auto_confirm_day = SettingHelper::getConfigValue('ORDER_AUTO_DELIVERY', 14);
                $time = Carbon::createFromTimeString($order_info->create_time)->addDays($auto_confirm_day)->toDateTimeString();
                return ['title' => '卖家已发货，买家待收货', 'desc' => '买家收到货后，请及时确认，系统将在 ' . $time . ' 之后自动确认'];
            case Order::ORDER_STATUS_IS_RECEIVED:
                $auto_confirm_day = SettingHelper::getConfigValue('ORDER_DELIVERY_COMPLETE_TIME', 7);
                $time = Carbon::createFromTimeString($order_info->sign_time)->addDays($auto_confirm_day)->toDateTimeString();
                return ['title' => '买家已收货', 'desc' => '买家收到货后，请及时确认完成，系统将在 ' . $time . ' 之后自动确认'];
            case Order::ORDER_STATUS_IS_COMPLETE:
                return ['title' => '订单已完成', 'desc' => '感谢您的购物，欢迎下次购买'];
            case Order::ORDER_STATUS_IS_CLOSED:
                return ['title' => '订单已关闭', 'desc' => '希望您的下次光临'];
            case Order::ORDER_STATUS_IS_REFUND:
                return ['title' => '订单退款中', 'desc' => '请及时关注退款情况'];
            default:
                return null;
        }
    }
}
