<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/9/19
 * Time: 10:43 AM
 */

namespace App\Http\Controllers\H5\Customer;

use App\Repositories\GoodsRepository;
use App\Repositories\ShopRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

class OrderController extends BaseController
{
    private $order, $attachment, $user, $promotion, $order_service, $goods, $shop;

    public function __construct(OrderRepository $order, AttachmentRepository $attachment, WechatFansRepository $user,
                                PromotionRepository $promotion, OrderService $order_service, GoodsRepository $goods, ShopRepository $shop)
    {
        parent::__construct();
        $this->order = $order;
        $this->attachment = $attachment;
        $this->user = $user;
        $this->promotion = $promotion;
        $this->goods = $goods;
        $this->shop = $shop;
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

        $goods_id = $request->input('goods_id');
        $goodsInfo = $this->goods->find($goods_id);
        if (!$goodsInfo) {
            return ResultHelper::json_error('商品不存在。');
        }
        $sku_id = $request->input('sku_id');
        $num = $request->input('num', 1);
        $shopInfo = $this->shop->find($goodsInfo->shop_id);


        $group_id = $request->input('group_id', 0);

        //示例：[{shop_id:1,pickup_id:0,shop_name:'',remark:'',pickup_time:'',goods:[{goods_id:1,sku_id:2,num:1}]}]
        //   $shop_goods = $goods ? json_decode($goods) : [];
        $pickup_id = $request->input('pickup_id', 0);
        if (empty($pickup_id)) {
            $pickups = $this->shop->getShopPickupByShopId($goodsInfo->shop_id);
            if ($pickups->count() > 0) {
                $pickup_id = $pickups[0]->id;
            } else {
                return ResultHelper::json_error('商家未设置提货点，暂时无法下单');
            }
        }

        $goods = [
            'shop_id' => $goodsInfo->shop_id,
            'shop_name' => $shopInfo->shop_name,
            'pickup_id' => $pickup_id,
            'remark' => '',
            'pickup_time' => \Carbon\Carbon::now()->addDays(1)->toDateTimeString()
        ];
        $goods['goods'][0] = [
            'shop_id' => $goodsInfo->shop_id,
            'goods_id' => $goods_id,
            'sku_id' => $sku_id,
            'sku_name' => $goodsInfo->goods_name,
            'goods_name' => $goodsInfo->goods_name,
            'num' => $num
        ];
        $goods = json_encode($goods);
        $shop_goods = $goods ? json_decode($goods) : [];
        if (empty($shop_goods)) {
            return ResultHelper::json_error('提交的商品为空');
        }
        //TODO 获取支付方式配置
        if ($type == 'pintuan') {
//            if (count($shop_goods) > 1 || count($shop_goods[0]->goods) > 1) {
//                return ResultHelper::json_error('创建拼团订单，只支持同时提交一个商品。');
//            }
            $result = $this->order->createPintuanGoodsOrder($this->uid, $shop_goods, Order::PAYMENT_TYPE_IS_YSEPAY, Order::ORDER_FROM_IS_H5, $username, $phone, $group_id);
        } else {
//            if (count($shop_goods) > 1) {
//                return ResultHelper::json_error('平台只支持同时提交同一商家多个商品，请重新提交订单。');
//            }
            $goodsData[0] = $shop_goods;
            $result = $this->order->createGoodsOrder($this->uid, $goodsData, Order::PAYMENT_TYPE_IS_YSEPAY, Order::ORDER_FROM_IS_H5, $username, $phone);
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

        $wxUserInfo = $this->wxUserInfo;
        if (empty($wxUserInfo)) {
            return ResultHelper::json_error('微信用户不存在');
        }

        return $this->order_service->getOrderPrePay($payment_type, $logInfo, $wxUserInfo, 'official');
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

}
