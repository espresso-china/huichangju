<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/11
 * Time: 6:54 PM
 */

namespace App\Http\Controllers\Wechat;

use Log;
use Carbon\Carbon;
use EasyWeChat\Factory as WeChatFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Model\OrderPayment;
use App\Repositories\OrderRepository;

class NotifyController extends BaseController
{

    private $order;

    public function __construct(OrderRepository $order)
    {
        $this->order = $order;
    }

    public function payOrderNotify(Request $request)
    {
        $WePay = WeChatFactory::payment(config('wechat.payment.master'));

        return $WePay->handlePaidNotify(function ($message, $fail) {
            Log::info('订单通知回调结果：');
            Log::info(json_encode($message));

            if ($message['result_code'] == 'SUCCESS' && $message['return_code'] == 'SUCCESS') {
                $attach = json_decode($message['attach']);
                // [
                //    'shopId' => $logInfo->shop_id,
                //    'logId' => $logInfo->id
                // ]
                try {
                    $logInfo = $this->order->getOrderPaymentInfoById($attach->logId);
                    if ($logInfo->pay_status == OrderPayment::PAY_STATUS_IS_SUCCESS) {
                        return true;
                    } else {
                        $pay_time = Carbon::createFromFormat('YmdHis', $message['time_end'])->timestamp;
                        $pay_money = $message['total_fee'] / 100;

                        $this->order->orderOnlinePay($logInfo->id, OrderPayment::PAYMENT_TYPE_IS_WXPAY, $pay_money, $pay_time);

                        //触发收银成功事件
                        //$orderInfo = $this->order->find($attach->orderId);
                        //Event::fire(new OrderRewardSuccessEvent($orderInfo, $logInfo));

                        return true;
                    }
                } catch (\Exception $exception) {
                    $fail($exception->getMessage());
                    return false;
                }
            }

            return false;
        });
    }
}
