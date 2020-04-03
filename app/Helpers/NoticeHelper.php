<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/27
 * Time: 10:44 AM
 */

namespace App\Helpers;


use App\Model\Member;
use App\Model\Notice;
use App\Model\User;

class NoticeHelper
{
    public static function send($notice_ids)
    {
        if (!is_array($notice_ids)) {
            $notice_ids = explode(',', $notice_ids);
        }
        foreach ($notice_ids as $notice_id) {
            $notice_info = Notice::where('id', $notice_id)->first();
            if ($notice_info && $notice_info->status == Notice::STATUS_IS_WAIT_SEND) {
                switch ($notice_info->notice_method) {
                    case 'email':
                        $result = self::sendEmail($notice_info);
                        break;
                    case 'sms':
                        $result = self::sendSms($notice_info);
                        break;
                    case 'minipg':
                        $result = self::sendMiniPgMsg($notice_info);
                        break;
                    default:
                        $result = self::sendNotice($notice_info);
                        break;
                }

                $notice_info->status = $result ? Notice::STATUS_IS_SUCCESS : Notice::STATUS_IS_FAIL;
                $notice_info->save();
            }
        }
    }

    public static function sendNotice(Notice $notice_info)
    {
        return false;
    }

    public static function sendEmail(Notice $notice_info)
    {
        return false;
    }

    public static function sendSms(Notice $notice_info)
    {
        // if ($notice_info->to_uid) {
        if (empty($notice_info->to)) {
            if ($notice_info->to_admin) {
                $phone = User::where('id', $notice_info->to_uid)->value('phone');
            } else {
                $phone = Member::where('uid', $notice_info->to_uid)->value('phone');
            }
        } else {
            $phone = $notice_info->to;
        }

        if ($phone) {
            $params = $notice_info->params ? json_decode($notice_info->params) : [];

            $templates = $notice_info->third_tpl_id ? json_decode($notice_info->third_tpl_id) : [];

            $content = self::getMessage('sms', $notice_info->notice_message, $params);

            $sms_params = self::getSmsParams($notice_info->notice_message, $params);

            $result = SmsHelper::sendTpl($phone, $content, $sms_params, $templates, true);

            return $result['success'];
        }
        //  }

        return false;
    }

    public static function getMessage($type, $tpl, $params)
    {
        $msg = $tpl;
        foreach ($params as $key => $val) {
            if ($type == 'sms') {
                $msg = str_replace('{' . $key . '}', $val, $msg);
            } else {
                $msg = str_replace($key, $val, $msg);
            }
        }
        return $msg;
    }

    public static function getSmsParams($tpl, $params)
    {
        if (is_object($params)) {
            $params = (array)$params;
        }

        $newParams = [];
        $keys = null;
        preg_match_all('/\{\w+\}/', $tpl, $keys);

        //\Log::info(json_encode($params));

        if (count($keys) && count($keys[0])) {
            foreach ($keys[0] as $key) {
                $key = trim($key, "{}");
                $newParams[$key] = isset($params[$key]) ? $params[$key] : '-';
            }
        }
        //\Log::info(json_encode($newParams));

        return $newParams;
    }

    public static function sendMiniPgMsg(Notice $notice_info)
    {
        return false;
    }
}
