<?php
/**
 * User: Tacker
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/4/7
 * Time: 18:42
 */

namespace Xndl\YsePay\Ledger;

use Closure;
use Xndl\YsePay\YsePay\Exceptions\Exception;
use Xndl\YsePay\YsePay\Http\ApiRequest;
use Xndl\YsePay\YsePay\Util\Util;
use Xndl\YsePay\YsePay\Util\Log;

class Ledger extends ApiRequest
{

    /**
     * 子账号注册配置
     */
    const REGISTER_METHOD = 'ysepay.merchant.register.accept';
    static $regRequest = [
        "merchant_no", "remark", "cust_type", "token", "another_name", "cust_name", "mer_flag",
        "industry", "province", "city", "company_addr", "legal_name", "legal_tel", "mail", "contact_man", "contact_phone",
        "legal_cert_type", "legal_cert_no", "legal_cert_expire", "bus_license", "bus_license_expire", "notify_type",
        "settle_type", "bank_account_no", "bank_account_name", "bank_account_type", "bank_card_type", "bank_name",
        "bank_type", "bank_province", "bank_city", "cert_type", "cert_no", "bank_telephone_no", "service_tel", "org_no", "sub_account_flag"
    ];
    static $regMustRequest = [
        "merchant_no", "remark", "cust_type", "token", "another_name", "cust_name", "mer_flag",
        "industry", "province", "city", "company_addr", "legal_name", "legal_tel", "mail", "contact_man", "contact_phone",
        "legal_cert_type", "legal_cert_no", "legal_cert_expire", "bus_license", "bus_license_expire", "notify_type",
        "settle_type", "bank_account_no", "bank_account_name", "bank_account_type", "bank_card_type", "bank_name",
        "bank_type", "bank_province", "bank_city", "cert_type", "cert_no", "bank_telephone_no", "service_tel", "org_no", "sub_account_flag"
    ];
    static $regNeedResponse = ["usercode", "custname", "custid", "user_status", "createtime"];

    //查询注册结果
    const QUERY_REGISTER_METHOD = 'ysepay.merchant.register.query';
    static $queryRegisterRequest = [];
    static $queryNeedResponse = [];

    /**
     * 获取注册资质TOKEN
     */
    const GET_TOKEN_METHOD = 'ysepay.merchant.register.token.get';
    static $getTokenMustRequest = [];
    static $getTokenRequest = [];
    static $getTokenNeedResponse = ["token", "token_status"];

    /**
     * 分账方资质上传接口配置
     */
    const UPLOAD_METHOD = 'upload';
    static $uploadPicTypes = [
        '00' => ['name' => '法人身份证正面', 'micro_need' => true, 'small_need' => true, 'company_need' => true,],
        '30' => ['name' => '法人身份证反面', 'micro_need' => true, 'small_need' => true, 'company_need' => true,],
        '19' => ['name' => '营业执照', 'micro_need' => false, 'small_need' => true, 'company_need' => true,],
        '20' => ['name' => '税务登记证', 'micro_need' => false, 'small_need' => false, 'company_need' => false,],
        '31' => ['name' => '客户协议', 'micro_need' => true, 'small_need' => true, 'company_need' => true,],
        '32' => ['name' => '授权书', 'micro_need' => false, 'small_need' => false, 'company_need' => false,],
        '33' => ['name' => '手持身份证正扫面照', 'micro_need' => false, 'small_need' => false, 'company_need' => false,],
        '34' => ['name' => '门头照', 'micro_need' => false, 'small_need' => false, 'company_need' => false,],
        '35' => ['name' => '结算银行卡正面照', 'micro_need' => true, 'small_need' => true, 'company_need' => false,],
        '36' => ['name' => '结算银行卡反面照', 'micro_need' => true, 'small_need' => true, 'company_need' => false,],
        '37' => ['name' => '开户许可证或印鉴卡', 'micro_need' => false, 'small_need' => false, 'company_need' => true,],
    ];
    static $uploadMustRequest = array("picType", "picFile", "token", 'superUsercode');
    static $uploadRequest = array("picType", "picFile", "token", 'superUsercode');
    static $uploadNeedResponse = array("isSuccess", "errorCode", "errorMsg", 'token');

    /**
     * 分账接口
     */
    const DIVIDE_METHOD = 'ysepay.single.division.online.accept';
    static $divideRequest = [];
    static $divideMustFillRequest = [];
    static $divideNeedResponse = [];

    /**
     * 分账查询
     */
    const DIVIDE_QUERY_METHOD = 'ysepay.single.division.online.query';
    static $divideQueryRequest = [];
    static $divideQueryMustFillRequest = [];
    static $divideQueryNeedResponse = [];

    /**
     * 注册
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function register(array $params)
    {
        //$params['merchant_no'] = $this->config['partner_id'];

        $this->setUrl(self::API_TYPE_REGISTER, self::REGISTER_METHOD, $this->config['register_notify_url']);
        $this->setNeedRequest(self::$regRequest);
        $this->setMustFillRequest(self::$regMustRequest);

        if (isset($params['legal_cert_no'])) {
            $params['legal_cert_no'] = Util::ECBEncrypt($params['legal_cert_no'], sprintf('%8.8s', $this->config['partner_id']));   //证件号。DES加密
        }

        if (isset($params['cert_no'])) {
            $params['cert_no'] = Util::ECBEncrypt($params['cert_no'], sprintf('%8.8s', $this->config['partner_id']));   //开户人证件号
        }

        $params['org_no'] = $this->config['sub_org_merchant_no'];
        $params['sub_account_flag'] = 'Y';

        $this->setFile('');
        $this->setPost($params);
        $this->setResponse(self::$regNeedResponse, 'ysepay_merchant_register_accept_response', true);
        $response = $this->send();
        return $response;
    }


    /**
     * 获取上传资质文件TOKEN
     * @return mixed
     * @throws Exception
     */
    public function getToken()
    {
        $this->setUrl(self::API_TYPE_REGISTER, self::GET_TOKEN_METHOD);
        $this->setPost([]);
        $this->setResponse(self::$getTokenNeedResponse, 'ysepay_merchant_register_token_get_response');
        $response = $this->send();
        return $response;
    }

    /**
     * 分账放上传资质
     * @param $picType
     * @param $picFile
     * @param $superUsercode
     * @param $token
     * @return \Illuminate\Support\Collection
     * @throws Exception
     */
    public function upload($picType, $picFile, $token)
    {
        if (is_dir($picFile) || !file_exists($picFile)) {
            throw new Exception('文件不存在');
        }

        $this->setUrl(self::API_TYPE_UPLOAD, self::UPLOAD_METHOD);
        $this->setNeedRequest(self::$uploadRequest);
        $this->setMustFillRequest(self::$uploadMustRequest);
        $this->setPost(['picType' => $picType, 'superUsercode' => $this->config['partner_id'], 'token' => $token]);
        $this->setFile($picFile);
        $this->setResponse('');
        $response = $this->send(false);
        return $response;
    }

    /**
     * 查询注册结果
     * @param $user_code
     * @return mixed
     * @throws Exception
     */
    public function queryRegister($user_code, $merchant_no = '')
    {
        if ($merchant_no) {
            $params = ['merchant_no' => $merchant_no];
        } else {
            $params = ['usercode' => $user_code];
        }
        $this->setUrl(self::API_TYPE_REGISTER, self::QUERY_REGISTER_METHOD, $this->config['register_notify_url']);
        $this->setPost($params);
        $this->setResponse([], 'ysepay_merchant_register_query_response', true);
        $response = $this->send();
        return $response;
    }

    /**
     * 分账接口
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function divide(array $params, $commission_money_total = 0.00, $commission_rate_total = 0.0000, $platform_assume_fee = false)
    {
        $params = array_merge($params, [
            "payee_usercode" => $this->config['partner_id'],
            //"org_no" => $this->config['partner_name'],
            "division_mode" => "02",
            "is_divistion" => "01",
        ]);

        if (empty($params['div_list'])) {
            return ['code' => 10000, 'msg' => 'Success', 'returnCode' => 9999, "retrunInfo" => '分账方不能为空'];
        }

        $division_count = count($params['div_list']);

        if ($division_count > 1) {
            //多个分账方，强行平台承担费用
            $platform_assume_fee = true;
        }

        //商家承担手续费
        if (!$platform_assume_fee) {
            $params['div_list'][0]['is_chargeFee'] = '01';
        }

        //存在佣金分配时
        if ($commission_money_total > 0) {
            $params['div_list'][] = [
                'div_amount' => $commission_money_total,
                'div_ratio' => $commission_rate_total,
                'division_mer_usercode' => $this->config['partner_id'],
                'is_chargeFee' => $platform_assume_fee ? '01' : '02',
            ];
        }

        $this->setUrl(self::API_TYPE_DIVISION, self::DIVIDE_METHOD, $this->config['division_notify_url']);
        $this->setPost($params);
        $this->setResponse(self::$divideNeedResponse, 'ysepay_single_division_online_accept_response', true);
        $response = $this->send();
        return $response;
    }

    /**
     * 分账查询
     * @param string $params
     * @return mixed
     * @throws Exception
     */
    public function divideQuery($out_trade_no)
    {
        $params = ['src_usercode' => $this->config['partner_id'], 'out_trade_no' => $out_trade_no];
        $this->setUrl(self::API_TYPE_DIVISION, self::DIVIDE_QUERY_METHOD, $this->config['division_notify_url']);
        $this->setPost($params);
        $this->setResponse(self::$divideQueryNeedResponse, 'ysepay_single_division_online_query_response', true);
        $response = $this->send();
        return $response;
    }

    private function balanceSource($user_code)
    {
        $this->setUrl(self::BALANCE_URL);
        $this->setNeedRequest(self::$balanceRequest);
        $this->setMustFillRequest(self::$balanceMustFillRequest);
        $this->setPost(['ledgerno' => $user_code]);
        return $response = $this->send();
    }

    /**
     * 查询平台余额
     * @return string
     */
    public function balanceMaster()
    {
        $data = $this->balanceSource('');
        return isset($data['balance']) ? $data['balance'] : "";
    }

    /**
     * 查询余额
     * @param $ledgerno
     * @return \Illuminate\Support\Collection
     * 查询账号余额
     */
    public function balance($user_code)
    {
        $data = $this->balanceSource($user_code);
        if (!isset($data['ledgerbalance'])) {
            return '';
        }
        $amount = explode(':', $data['ledgerbalance']);
        return $amount[1];
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
