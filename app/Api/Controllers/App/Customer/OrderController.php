<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/5
 * Time: 12:05 PM
 */

namespace App\Api\Controllers\App\Customer;

use Carbon\Carbon;
use Illuminate\Http\Request;
use EasyWeChat\Factory as WeChatFactory;
use EasyWeChat\Kernel\Support;
use App\Helpers\ResultHelper;
use App\Helpers\SettingHelper;
use App\Helpers\UrlHelper;
use App\Model\Order;
use App\Model\OrderPayment;
use App\Model\OrderPintuan;
use App\Model\OrderRefund;
use App\Repositories\PromotionRepository;
use App\Repositories\WechatFansRepository;
use App\Repositories\AttachmentRepository;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use App\Api\Controllers\App\ApiController;

class OrderController extends ApiController
{
    private $order, $attachment, $user, $promotion, $order_service;

    public function __construct(OrderRepository $order, AttachmentRepository $attachment, WechatFansRepository $user,
                                PromotionRepository $promotion, OrderService $order_service)
    {
        parent::__construct();
        $this->order = $order;
        $this->attachment = $attachment;
        $this->user = $user;
        $this->promotion = $promotion;
        $this->order_service = $order_service;
    }

    public function submitGoodsOrder(Request $request)
    {
        $type = $request->input('type', 'normal'); // 普通订单normal,拼团订单pintuan,

        $username = $request->input('username');
        $phone = $request->input('phone');

        if (strlen($phone) != 11) {
            return ResultHelper::json_error('手机号码格式不正确');
        }

        $goods = $request->input('goods');

        $group_id = $request->input('group_id', 0);

        //示例：[{shop_id:1,pickup_id:0,shop_name:'',remark:'',pickup_time:'',goods:[{goods_id:1,sku_id:2,num:1}]}]
        $shop_goods = $goods ? json_decode($goods) : [];
        if (empty($shop_goods)) {
            return ResultHelper::json_error('提交的商品为空');
        }

        //TODO 获取支付方式配置
        if ($type == 'pintuan') {
            if (count($shop_goods) > 1 || count($shop_goods[0]->goods) > 1) {
                return ResultHelper::json_error('创建拼团订单，只支持同时提交一个商品。');
            }
            $result = $this->order->createPintuanGoodsOrder($this->uid, $shop_goods[0], Order::PAYMENT_TYPE_IS_YSEPAY, Order::ORDER_FROM_IS_WX_APP, $username, $phone, $group_id);
        } elseif ($type == 'kanjia') {
            if (count($shop_goods) > 1 || count($shop_goods[0]->goods) > 1) {
                return ResultHelper::json_error('创建砍价订单，只支持同时提交一个商品。');
            }
            $result = $this->order->createKanjiaGoodsOrder($this->uid, $shop_goods[0], Order::PAYMENT_TYPE_IS_YSEPAY, Order::ORDER_FROM_IS_WX_APP, $username, $phone, $group_id);
        } else {
            if (count($shop_goods) > 1) {
                return ResultHelper::json_error('平台只支持同时提交同一商家多个商品，请重新提交订单。');
            }
            $result = $this->order->createGoodsOrder($this->uid, $shop_goods, Order::PAYMENT_TYPE_IS_YSEPAY, Order::ORDER_FROM_IS_WX_APP, $username, $phone);
        }

        if ($result['status']) {
            if (count($shop_goods) > 1) {
                return ResultHelper::json_result('订单创建成功，您选购了多个商家的商品，系统自动为每个商家创建了独立的订单，但您可以一并付款结算。', $result['data'], 200);
            } else {
                return ResultHelper::json_result('订单创建成功', $result['data'], 200);
            }
        } else {
            return ResultHelper::json_error($result['msg']);
        }
    }

    public function getGoodsOrderInfo(Request $request)
    {
        $order_id = $request->input('order_id');
        $out_trade_no = $request->input('out_trade_no');

        $order_data = [];
        if ($order_id > 0) {
            $order_info = $this->order->find($order_id);
            $order_data[] = $this->getGoodsOrderData($order_info);
            $order_payment_info = $this->order->getOrderPaymentInfoByOutTradeNo($order_info->out_trade_no);
        } else {
            $orders = $this->order->getOrdersByOutTradeNo($out_trade_no);
            foreach ($orders as $order_info) {
                $order_data[] = $this->getGoodsOrderData($order_info);
            }
            $order_payment_info = $this->order->getOrderPaymentInfoByOutTradeNo($out_trade_no);
        }

        if (empty($order_data)) {
            return ResultHelper::json_error('订单信息不存在');
        }

        if (empty($order_payment_info)) {
            return ResultHelper::json_error('数据错误，订单支付记录不存在');
        }

        $order_payment_info->account_name = '复美集团';

        //TODO 退款信息

        $data = [
            'orders' => $order_data,
            'payment' => $order_payment_info,
        ];

        return ResultHelper::json_result('success', $data, 200);
    }

    private function getGoodsOrderData($order_info)
    {
        $data = [
            'order_id' => $order_info->order_id,
            'order_no' => $order_info->order_no,
            'shop_id' => $order_info->shop_id,
            'shop_name' => $order_info->shop_name,
            'remark' => $order_info->buyer_message,
            'create_time' => $order_info->create_time->toDateTimeString(),
            'shipping_time' => $order_info->shipping_time,
            'goods_money' => $order_info->goods_money,
            'order_money' => $order_info->order_money,
            'give_point' => $order_info->give_point,
            'order_status' => $order_info->order_status,
            'pay_status' => $order_info->pay_status,
            'shipping_status' => $order_info->shipping_status,
            'order_status_name' => $order_info->order_status_name,
            'pay_status_name' => $order_info->pay_status_name,
            'shipping_status_name' => $order_info->shipping_status_name,
            'pickup' => null,
            'express' => null,
            'goods' => [],
            'actions' => $order_info->actions['member_operation'],
            'tips' => $this->getOrderTips($order_info),
        ];
        if ($order_info->shipping_type == Order::SHIPPING_TYPE_IS_SELF) {
            $pickup_info = $this->order->getOrderPickupInfoByOrderId($order_info->order_id);
            if ($pickup_info) {
                $data['pickup'] = [
                    'id' => $pickup_info->id,
                    'name' => $pickup_info->name,
                    'contact' => $pickup_info->contact,
                    'phone' => $pickup_info->phone,
                    'address' => $pickup_info->address,
                    'buyer_name' => $pickup_info->buyer_name,
                    'buyer_mobile' => $pickup_info->buyer_mobile,
                ];
            }
        }

        $order_goods = $this->order->getOrderGoodsByOrderId($order_info->order_id);
        foreach ($order_goods as $goods) {

            $data['goods'][] = [
                'order_goods_id' => $goods->order_goods_id,
                'goods_id' => $goods->goods_id,
                'goods_name' => $goods->goods_name,
                'sku_id' => $goods->sku_id,
                'sku_name' => $goods->sku_name,
                'price' => $goods->price,
                'money' => $goods->goods_money,
                'num' => $goods->num,
                'picture' => $goods->picture->qiniu_url ?: ''
            ];
        }

        return $data;
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

    public function closeGoodsOrder(Request $request)
    {
        $order_id = $request->input('order_id');
        $out_trade_no = $request->input('out_trade_no');

        if ($order_id > 0) {
            $this->order->orderClose($order_id, $this->uid, false);
        } else {
            $orders = $this->order->getOrdersByOutTradeNo($out_trade_no);
            foreach ($orders as $order_info) {
                $this->order->orderClose($order_info->order_id, $this->uid, false);
            }
        }

        return ResultHelper::json_result('订单已关闭', '', 200);
    }

    public function deleteGoodsOrder(Request $request)
    {

        $order_id = $request->input('order_id');
        $out_trade_no = $request->input('out_trade_no');

        if ($order_id > 0) {
            $this->order->orderDelete($order_id, $this->uid, false);
        } else {
            $orders = $this->order->getOrdersByOutTradeNo($out_trade_no);
            foreach ($orders as $order_info) {
                $this->order->orderDelete($order_info->order_id, $this->uid, false);
            }
        }

        return ResultHelper::json_result('订单已删除', '', 200);
    }

    public function completeGoodsOrder(Request $request)
    {
        $order_id = $request->input('order_id');

        if (empty($order_id)) {
            return ResultHelper::json_error('订单参数ID不能为空');
        }

        $this->order->orderComplete($order_id, $this->uid, false);

        return ResultHelper::json_result('订单交易完成', '', 200);
    }

    public function refundGoodsOrder(Request $request)
    {
        $order_id = $request->input('order_id');
        $order_goods_id = $request->input('order_goods_id');

        $refund_money = $request->input('money');
        $refund_reason = $request->input('reason');

        if (empty($order_id)) {
            return ResultHelper::json_error('订单参数ID不能为空');
        }

        if (empty($refund_money)) {
            return ResultHelper::json_error('退款金额不能为空');
        }

        if (empty($refund_reason)) {
            return ResultHelper::json_error('退款原因不能为空');
        }

        $result = $this->order->orderGoodsRefundApply($order_id, $order_goods_id, $refund_money, $refund_reason, $this->uid, OrderRefund::USER_TYPE_IS_MEMBER);

        if ($result['status']) {
            return ResultHelper::json_result($result['msg']);
        } else {
            return ResultHelper::json_error($result['msg']);
        }
    }

    public function getGoodsOrders(Request $request)
    {
        $uid = $request->input('uid');
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
                \Log::info($goods);
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
                'order_status' => $order->order_status,
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
        $result = [
            'orders' => $orders_data,
        ];
        return ResultHelper::json_result('请求成功', $result, 200);
    }

    public function getOrderPrePay(Request $request)
    {
        $out_trade_no = $request->input('out_trade_no');
        $payment_type = $request->input('payment_type', OrderPayment::PAYMENT_TYPE_IS_YSEPAY); //手动设定在线支付方式

        $logInfo = $this->order->getOrderPaymentInfoByOutTradeNo($out_trade_no);

        if (empty($logInfo)) {
            return ResultHelper::json_error('订单支付信息不存在');
        } else {
            if ($logInfo->pay_status == OrderPayment::PAY_STATUS_IS_SUCCESS) {
                return ResultHelper::json_error('订单已支付成功', null, 201);
            }

            //强制检测是否微信支付订单
            $pay_result = $this->checkPayOrder(null, $logInfo);
            if ($pay_result['status']) {
                $pay_money = $pay_result['data']['money'];
                $pay_time = $pay_result['data']['time'];
                $pay_fee = $pay_result['data']['fee'];

                $this->order->orderOnlinePay($logInfo->id, $payment_type, $pay_money, $pay_time, $pay_fee);

                return ResultHelper::json_result('订单已支付成功', $logInfo, 201);
            }
        }

        $wxUserInfo = $this->user->getWeChatUserBySession3rd($this->session3rd);
        if (empty($wxUserInfo)) {
            \Log::error('微信用户不存在，Session: ' . $this->session3rd);
            return ResultHelper::json_error('微信用户不存在');
        }

        return $this->order_service->getOrderPrePay($payment_type, $logInfo, $wxUserInfo);
    }

    private function checkPayOrder($orderInfo, $logInfo)
    {
        sleep(3);
        return $this->order_service->checkPayOrder($orderInfo, $logInfo);
    }

    public function payGoodsOrder(Request $request)
    {
        $out_trade_no = $request->input('out_trade_no');
        $logInfo = $this->order->getOrderPaymentInfoByOutTradeNo($out_trade_no);
        if (empty($logInfo)) {
            return ResultHelper::json_error('支付记录不存在');
        }

        if ($logInfo->pay_status == OrderPayment::PAY_STATUS_IS_SUCCESS) {
            return ResultHelper::json_error('订单已支付成功', null, 201);
        }

        //强制检测是否微信支付订单
        $pay_result = $this->checkPayOrder(null, $logInfo);
        if ($pay_result['status']) {
            $pay_money = $pay_result['data']['money'];
            $pay_time = $pay_result['data']['time'];
            $pay_fee = $pay_result['data']['fee'];

            $this->order->orderOnlinePay($logInfo->id, OrderPayment::PAYMENT_TYPE_IS_YSEPAY, $pay_money, $pay_time, $pay_fee);

            //触发收银成功事件
            //Event::fire(new OrderRewardSuccessEvent($logInfo->order, $logInfo));
            return ResultHelper::json_result('订单支付成功', null, 200);
        } else {
            return ResultHelper::json_error($pay_result['msg']);
        }

    }

    public function exchangeGoods(Request $request)
    {
        $goods_id = $request->input('id');

        //TODO 获取支付方式配置

        $result = $this->order->createExchangeGoodsOrder($this->uid, $goods_id, 1, Order::PAYMENT_TYPE_IS_YSEPAY, Order::ORDER_FROM_IS_WX_APP);

        if ($result['status']) {
            return ResultHelper::json_result('兑换成功', $result['data'], 200);
        } else {
            return ResultHelper::json_error($result['msg']);
        }
    }

    public function getOrderMessage(Request $request)
    {
        $uid = $request->input('uid');
        $page = $request->input('page', 0);
        $size = $request->input('size', 5);
        $page--;
        $result = $this->order->getOrderActionsByBuyerId($uid, $page, $size);
        $data = [];
        foreach ($result as $key => $v) {
            $data[$key]['info'] = $v;
            $data[$key]['goods'] = $this->order->getOrderGoodsByOrderId($v->order_id);
        }

        return ResultHelper::json_result('success', $data, 200);
    }

    public function delOrderMessage(Request $request)
    {
        $uid = $request->input('uid');
        $action_id = $request->input('action_id');
        $result = $this->order->delOrderAction($action_id);
        return ResultHelper::json_result('删除成功', $result, 200);
    }
}
