<?php
/**
 * Created by PhpStorm.
 * User: Tacker
 * Date: 2017-04-22
 * Time: 17:00
 */

namespace App\Helpers;

use Carbon\Carbon;
use EasyWeChat\Factory;

//use App\Models\WechatTemplateLogs;
//use App\Models\WechatFormIdLog;

class WeChatHelper
{

    public static function getBankCode($bank_name)
    {
        if (isset(self::getBankCodes()[$bank_name])) {
            return self::getBankCodes()[$bank_name];
        } else {
            return '';
        }
    }

    public static function getBanks()
    {
        return array_keys(self::getBankCodes());
    }

    public static function getBankCodes()
    {
        return [
            '工商银行' => '1002',
            '农业银行' => '1005',
            '中国银行' => '1026',
            '建设银行' => '1003',
            '招商银行' => '1001',
            '邮储银行' => '1066',
            '交通银行' => '1020',
            '浦发银行' => '1004',
            '民生银行' => '1006',
            '兴业银行' => '1009',
            '平安银行' => '1010',
            '中信银行' => '1021',
            '华夏银行' => '1025',
            '广发银行' => '1027',
            '光大银行' => '1022',
            '北京银行' => '1032',
            '宁波银行' => '1056',
        ];
    }

    public static function getConfigs($configs = array())
    {
//        return [
//            'debug' => env('APP_DEBUG', true),
//            'app_id' => env('WECHAT_MINI_PROGRAM_APPID', 'wx9816d76df5d2e070'),
//            'secret' => env('WECHAT_MINI_PROGRAM_SECRET', '11d04306a22bf18f3512725edcd293dd'),
//            'token' => env('WECHAT_TOKEN'),
//            'aes_key' => '', // 可选
//            'payment' => [
//                'merchant_id' => env('MERCHANT_ID'),
//                'key' => env('MERCHANT_KEY'),
//                'cert_path' => storage_path(env('WECHAT_CERT_PATH')),
//                'key_path' => storage_path(env('WECHAT_KEY_PATH')),
//                'notify_url' => env('NOTIFY_URL'),
//                'device_info' => 'WEB',
//            ],
//        ];
        return config('wechat.mini_program');
    }

    public static function userTextEncode($str)
    {
        if (!is_string($str)) return $str;
        if (!$str || $str == 'undefined') return '幸孕淘淘';

        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
            return addslashes($str[0]);
        }, $text);
        return json_decode($text);
    }

    /**
     *解码上面的转义
     */
    public static function userTextDecode($str)
    {
        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback('/\\\\\\\\/i', function ($str) {
            return '\\';
        }, $text); //将两条斜杠变成一条，其他不动
        return json_decode($text);
    }

    public static function sendTplMsg($openid, $templateId, $form_id, $data, $page = '/pages/home/index')
    {
        $app = Factory::miniProgram(self::getConfigs());

        $res = $app->template_message->send([
            'touser' => $openid,
            'template_id' => $templateId,
            'page' => $page,
            'form_id' => $form_id,
            'data' => $data
        ]);

//        $result = WechatTemplateLogs::create([
//            'touser' => $openid,
//            'template_id' => $templateId,
//            'form_id' => $form_id,
//            'data' => serialize($data),
//            'color' => '',
//            'created_at' => time(),
//            'updated_at' => time()
//        ]);

        return $res;
    }

    public static function getTemplate()
    {
        $app = Factory::miniProgram(self::getConfigs());
        return $app->template_message->getTemplates(0, 20);
    }

    public static function saveFormId($wxUid, $formId, $openid = '')
    {
        \Log::info('formId:' . $formId);

        if (empty($formId) || $formId == 'the formId is a mock one') {
            return false;
        }
//
//        $logCount = WechatFormIdLog::where('wx_uid', $wxUid)
//            ->where('created_at', '>', Carbon::now()->addDays(-6)->timestamp)
//            ->where('used', 0)
//            ->count();
//        if ($logCount < 20) {
//            $data = ['wx_uid' => $wxUid, 'formid' => $formId, 'openid' => $openid, 'used' => 0];
//            return WechatFormIdLog::create($data);
//        }
        return false;
    }

    public static function getFormId($wxUid)
    {
//        $logInfo = WechatFormIdLog::where('wx_uid', $wxUid)
//            ->where('created_at', '>', Carbon::now()->addDays(-6)->timestamp)
//            ->where('used', 0)
//            ->first();
//
//        if ($logInfo) {
//            $logInfo->used = 1;
//            $logInfo->save();
//            return $logInfo->formid;
//        }
        return '';
    }

    public static function payFxUserWithdraw($withdraw_no, $bank_name, $account, $username, $amount)
    {
        $app = Factory::payment(config('wechat.payment.master'));

        if ($bank_name == '微信支付') {

            $query = $app->transfer->queryBalanceOrder($withdraw_no);
            $query = self::parseResult($query);
            if ($query['status']) {
                return ['status' => false, 'msg' => $query['msg']];
            }

            if ($username) {
                $result = $app->transfer->toBalance([
                    'partner_trade_no' => $withdraw_no, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                    'openid' => $account,
                    'check_name' => 'FORCE_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                    're_user_name' => $username, // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
                    'amount' => intval($amount * 100), // 企业付款金额，单位为分
                    'desc' => $username . '佣金提现：' . $amount . '元', // 企业付款操作说明信息。必填
                ]);
            } else {
                $result = $app->transfer->toBalance([
                    'partner_trade_no' => $withdraw_no, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                    'openid' => $account,
                    'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                    'amount' => intval($amount * 100), // 企业付款金额，单位为分
                    'desc' => $username . '佣金提现：' . $amount . '元', // 企业付款操作说明信息。必填
                ]);
            }
        } else {
            $query = $app->transfer->queryBankCardOrder($withdraw_no);
            $query = self::parseResult($query);

            if ($query['status']) {
                return ['status' => false, 'msg' => $query['msg']];
            }

            $bank_code = self::getBankCode($bank_name);
            if (empty($bank_code)) {
                return ['status' => false, 'msg' => '提现不支持此银行卡'];
            }

            $result = $app->transfer->toBankCard([
                'partner_trade_no' => $withdraw_no,
                'enc_bank_no' => $account, // 银行卡号
                'enc_true_name' => $username,   // 银行卡对应的用户真实姓名
                'bank_code' => $bank_code, // 银行编号
                'amount' => intval($amount * 100), // 付款金额，单位为分
                'desc' => $username . '的佣金提现：' . $amount . '元', // 付款操作说明信息。必填
            ]);
        }

        return self::parseResult($result);
    }

    public static function payShopWithdraw($withdraw_no, $bank_name, $account, $username, $amount)
    {
        $app = Factory::payment(config('wechat.payment.master'));

        $query = $app->transfer->queryBankCardOrder($withdraw_no);
        $query = self::parseResult($query);
        if ($query['status']) {
            return ['status' => false, 'msg' => $query['msg']];
        }

        $bank_code = self::getBankCode($bank_name);
        if (empty($bank_code)) {
            return ['status' => false, 'msg' => '提现不支持此银行卡'];
        }

        $result = $app->transfer->toBankCard([
            'partner_trade_no' => $withdraw_no,
            'enc_bank_no' => $account, // 银行卡号
            'enc_true_name' => $username,   // 银行卡对应的用户真实姓名
            'bank_code' => $bank_code, // 银行编号
            'amount' => intval($amount * 100), // 付款金额，单位为分
            'desc' => $username . '的店铺提现：' . $amount . '元', // 付款操作说明信息。必填
        ]);

        return self::parseResult($result);
    }

    public static function parseResult($result)
    {
        \Log::info(json_encode($result));

        if ($result['return_code'] == 'FAIL') {
            return ['status' => false, 'msg' => $result['return_msg'], 'code' => ''];
        }

        if (isset($result['result_code']) && $result['result_code'] == 'FAIL') {
            return ['status' => false, 'msg' => $result['err_code_des'], 'code' => $result['err_code']];
        }

        return ['status' => true, 'msg' => $result['return_msg'], 'data' => $result];
    }

}
