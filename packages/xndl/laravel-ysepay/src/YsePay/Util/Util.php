<?php
/**
 * User: Tacker
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/3/30
 * Time: 10:30
 */

namespace Xndl\YsePay\YsePay\Util;

class Util
{
    public static function getHmac(array $dataArray, $key)
    {
        if (is_array($dataArray)) {

            $data = implode("", $dataArray);
        } else {

            $data = strval($dataArray);
        }
        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack("H*", md5($k_ipad . $data)));
    }


    /**
     * @使用特定function对数组中所有元素做处理
     * @&$array 要处理的字符串
     * @$function 要执行的函数
     * @$apply_to_keys_also 是否也应用到key上
     *
     */
    public static function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
    }

    /**
     *
     * @将数组转换为JSON字符串（兼容中文）
     * @$array 要转换的数组
     * @return string 转换得到的json字符串
     *
     */
    public static function cn_json_encode($array)
    {
        $array = self::cn_url_encode($array);
        $json = json_encode($array);
        return urldecode($json);
    }

    /**
     *
     * @将数组统一进行urlencode（兼容中文）
     * @$array 要转换的数组
     * @return array 转换后的数组
     *
     */
    public static function cn_url_encode($array)
    {
        self::arrayRecursive($array, "urlencode", true);
        return $array;
    }


    /**
     * @取得aes加密
     * @$dataArray 明文字符串
     * @$key 密钥
     * @return string
     *
     */
    public static function getAes($data, $aesKey)
    {

        //print_r(mcrypt_list_algorithms());
        //print_r(mcrypt_list_modes());
        $aes = new CryptAES();

        $aes->set_key($aesKey);
        $aes->require_pkcs5();
        $encrypted = strtoupper($aes->encrypt($data));

        return $encrypted;

    }

    /**
     * @取得aes解密
     * @$dataArray 密文字符串
     * @$key 密钥
     * @return string
     *
     */
    public static function getDeAes($data, $aesKey)
    {

        $aes = new CryptAES();
        $aes->set_key($aesKey);
        $aes->require_pkcs5();
        $text = $aes->decrypt($data);
        return $text;
    }

    /**
     * @检查一个数组是否是有效的
     * @$checkArray 数组
     * @$arrayKey 数组索引
     * @return boolean
     * 如果$arrayKey传参，则不止检查数组，
     * 而且检查索引是否存在于数组中。
     *
     */
    public static function isViaArray($checkArray, $arrayKey = null)
    {

        if (!$checkArray || empty($checkArray)) {

            return false;
        }

        if (!$arrayKey) {

            return true;
        }

        return array_key_exists($arrayKey, $checkArray);
    }

    /**
     * 验签转明码
     * @param input check
     * @param input msg
     * @return data
     * @return success
     */
    public static function sign_check($sign, $data, $config)
    {
        $certificateCAcerContent = file_get_contents($config['business_gate_cert_path']);//公钥
        $certificateCApemContent = '-----BEGIN CERTIFICATE-----' . PHP_EOL . chunk_split(base64_encode($certificateCAcerContent), 64, PHP_EOL) . '-----END CERTIFICATE-----' . PHP_EOL;
        // 签名验证
        $success = openssl_verify($data, base64_decode($sign), openssl_get_publickey($certificateCApemContent), OPENSSL_ALGO_SHA1);
        return $success;
    }

    /**
     * 签名加密
     * @param input data
     * @return success
     * @return check
     * @return msg
     */
    public static function sign_encrypt($input, $config)
    {
        $return = [
            'success' => 0,
            'msg' => '',
            'check' => ''
        ];

        $pkcs12 = file_get_contents($config['pfx_path']); //私钥
        if (openssl_pkcs12_read($pkcs12, $certs, $config['pfx_password'])) {
            $privateKey = $certs['pkey'];
            //$publicKey = $certs['cert'];
            $signedMsg = "";
            if (openssl_sign($input['data'], $signedMsg, $privateKey, OPENSSL_ALGO_SHA1)) {
                $return['success'] = 1;
                $return['check'] = base64_encode($signedMsg);
                $return['msg'] = base64_encode($input['data']);
            }
        }

        return $return;
    }

    //签名
    public static function sign($data, $config)
    {
        ksort($data);
        $signStr = "";
        foreach ($data as $key => $val) {
            $signStr .= $key . '=' . $val . '&';
        }
        $signStr = trim($signStr, '&');
        $sign = self::sign_encrypt(array('data' => $signStr), $config);
        return trim($sign['check']);
    }

    //验签
    public static function sign_verify($data, $config)
    {
        //返回的数据处理
        $sign = trim($data['sign']);
        unset($data['sign']);
        ksort($data);
        $url = "";
        foreach ($data as $key => $val) {
            /* 验证签名 */
            if ($val) $url .= $key . '=' . $val . '&';
        }
        $str = trim($url, '&');
        if (self::sign_check($sign, $str, $config) != true) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * DES加密方法
     * @param $data 传入需要加密的证件号码
     * @return string 返回加密后的字符串
     */
    public static function ECBEncrypt($data, $key)
    {
        $encrypted = openssl_encrypt($data, 'DES-ECB', $key, 1);
        return base64_encode($encrypted);
    }

    /**
     * DES解密方法
     * @param $data 传入需要解密的字符串
     * @return string 返回解密后的证件号码
     */
    public static function ECBDecrypt($data, $key)
    {
        $encrypted = base64_decode($data);
        $decrypted = openssl_decrypt($encrypted, 'DES-ECB', $key, 1);
        return $decrypted;
    }
}
