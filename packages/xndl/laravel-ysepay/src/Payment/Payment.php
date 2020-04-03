<?php
/**
 * User: Tacker
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/4/7
 * Time: 18:36
 */

namespace Xndl\YsePay\Payment;

use Closure;
use Xndl\YsePay\YsePay\Exceptions\Exception;
use Xndl\YsePay\YsePay\Http\ApiRequest;
use Xndl\YsePay\YsePay\Util\Util;
use Xndl\YsePay\YsePay\Util\Log;

class Payment extends ApiRequest
{

    /**
     * 创建支付
     */
    const PAY_METHOD = 'ysepay.online.weixin.pay';
    static $payRequest = [
        'out_trade_no', 'shopdate', 'subject', 'total_amount', 'currency', 'seller_id', 'seller_name', 'timeout_express',
        'extend_params', 'extra_common_param', 'business_code', 'sub_openid', 'is_minipg', 'appid', 'sub_merchant',
        'merName', 'merShortName', 'merAddr', 'telephone', 'merNo', 'category', 'mrchntCertId',
        'consignee_info', 'consigneeName', 'consigneeAddr', 'transportationInfo', 'commodityName', 'commodityNumber',
    ];
    static $payMustFillRequest = [
        'out_trade_no', 'shopdate', 'subject', 'total_amount', 'currency', 'seller_id', 'seller_name', 'timeout_express',
        'extend_params', 'extra_common_param', 'business_code', 'sub_openid', 'is_minipg', 'appid',
    ];
    static $payNeedResponse = [
        'out_trade_no', 'trade_no', 'trade_status', 'total_amount', 'currency', 'jsapi_pay_info',
    ];

    /**
     * 查询支付
     */
    const QUERY_METHOD = 'ysepay.online.trade.query';
    static $queryRequest = ['out_trade_no', 'shopdate', 'trade_no'];
    static $queryMustFillRequest = ['out_trade_no', 'shopdate'];
    static $queryResponse = ['trade_status', 'out_trade_no', 'trade_no', 'total_amount', 'receipt_amount', 'account_date'];


    const REFUND_METHOD = 'ysepay.online.trade.refund';
    static $refundRequest = [];
    static $refundMustFillRequest = [];
    static $refundResponse = [];

    const DIVISION_REFUND_METHOD = 'ysepay.online.trade.refund.split';
    static $divisionRefundRequest = [];
    static $divisionRefundMustFillRequest = [];
    static $divisionRefundResponse = [];

    const DIVIDE_BILL_METHOD = 'ysepay.online.division.downloadurl.get';
    static $divideBillNeedResponse = [];

    /***
     * 创建支付订单接口
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function add(array $params)
    {
        $params = array_merge($params, [
            'seller_id' => $this->config['partner_id'],
            'seller_name' => $this->config['partner_name'],
            'timeout_express' => $this->config['pay_timeout_express'],
            'business_code' => $this->config['business_code'],
        ]);
        $this->setUrl(self::API_TYPE_PAYMENT, self::PAY_METHOD, $this->config['pay_notify_url']);
        $this->setNeedRequest(self::$payRequest);
        $this->setMustFillRequest(self::$payMustFillRequest);
        $this->setResponse(self::$payNeedResponse, 'ysepay_online_weixin_pay_response', true);
        $this->setPost($params);
        $response = $this->send();
        return $response;
    }

    /**
     * 查询支付状态
     */
    public function query($orderNo, $shopdate)
    {
        $this->setUrl(self::API_TYPE_PAYMENT_QUERY, self::QUERY_METHOD);
        $this->setNeedRequest(self::$queryRequest);
        $this->setMustFillRequest(self::$queryMustFillRequest);
        $this->setPost(['out_trade_no' => $orderNo, 'shopdate' => $shopdate]);
        //if ($orderNo == '15607395071581') {
        $this->setResponse(self::$queryResponse, 'ysepay_online_trade_query_response', true);
        //} else {
        //    $this->setResponse(self::$queryResponse, 'ysepay_online_trade_query_response', false);
        //}
        $response = $this->send();
        return $response;
    }

    /**
     * 订单退款接口
     * @param $out_trade_no 订单号
     * @param $refund_no 退款编号
     * @param $refund_amount 退款金额
     * @param $refund_reason 退款缘由
     * @param array $divisions
     * @return mixed
     * @throws Exception
     */
    public function refund($out_trade_no, $refund_no, $refund_amount, $refund_reason, $divisions = [])
    {
        $params = array(
            "out_trade_no" => $out_trade_no,
            //"trade_no" => $trade_no,
            "refund_amount" => $refund_amount,
            "refund_reason" => $refund_reason,
            "out_request_no" => $refund_no,
            'is_division' => $divisions ? '01' : '02',
            'ori_division_mode' => '02',
        );

        if ($divisions) {
            $params['refund_split_info'] = $divisions;
        }

        $this->setUrl(self::API_TYPE_PAYMENT_QUERY, self::REFUND_METHOD);
        $this->setNeedRequest(self::$refundRequest);
        $this->setMustFillRequest(self::$refundMustFillRequest);
        $this->setPost($params);
        $this->setResponse(self::$refundResponse, 'ysepay_online_trade_refund_response', true);
        $response = $this->send();
        return $response;
    }

    /**
     * 订单退款接口
     * @param $out_trade_no 订单号
     * @param $refund_no 退款编号
     * @param $refund_amount 退款金额
     * @param $refund_reason 退款缘由
     * @param array $divisions
     * @return mixed
     * @throws Exception
     */
    public function divisionRefund($out_trade_no, $refund_no, $refund_amount, $refund_reason, $divisions = [], $order_divisions = [])
    {
        $params = array(
            "out_trade_no" => $out_trade_no,
            //"trade_no" => $trade_no,
            "refund_amount" => $refund_amount,
            "refund_reason" => $refund_reason,
            "out_request_no" => $refund_no,
            'is_division' => $divisions ? '01' : '02',
            'ori_division_mode' => '02',
        );

        if ($divisions) {
            $params['refund_split_info'] = $divisions;
        }

        $this->setUrl(self::API_TYPE_PAYMENT_QUERY, self::DIVISION_REFUND_METHOD);
        $this->setNeedRequest(self::$divisionRefundRequest);
        $this->setMustFillRequest(self::$divisionRefundMustFillRequest);
        $this->setPost($params);
        $this->setResponse(self::$divisionRefundResponse, 'ysepay_online_trade_refund_split_response', true);
        $response = $this->send();
        return $response;
    }

    public function downBill($date)
    {
        $params = ['account_date' => $date];
        $this->setUrl(self::API_TYPE_PAYMENT, self::DIVIDE_BILL_METHOD);
        $this->setPost($params);
        $this->setResponse(self::$divideBillNeedResponse, 'ysepay_online_division_downloadurl_get_response', true);
        $response = $this->send(true, false);
        if ($response['code'] == 'SUCCESS') {
            return $response['bill_download_url'];
        } else {
            return '';
        }
    }

    public function callback(Closure $callback)
    {
        $data = request()->input();

        Log::info('Notify: ' . json_encode($data));

        if (!$data) {
            //throw new Exception('没有数据');
            Log::error('Notify: 没有数据');
            return response('没有数据');
        }

        //$data = json_decode($data, true);
        if (!Util::sign_verify($data, $this->config)) {
            //throw new Exception('签名错误');
            Log::error('Notify: 签名错误');
            return response('签名错误');
        }

        $result = \call_user_func($callback, $data);

        if ($result) {
            return response('success');
        } else {
            return response('fail');
        }
    }

}
