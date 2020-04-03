<?php
/**
 * User: Tacker
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/3/29
 * Time: 21:32
 */

namespace Xndl\YsePay\YsePay\Http;

use Xndl\YsePay\Config;
use Xndl\YsePay\Exception\BusinessException;
use Xndl\YsePay\YsePay\Exceptions\Exception;
use Xndl\YsePay\YsePay\Util\Util;
use Xndl\YsePay\YsePay\Util\Log;

class ApiRequest
{
    const API_TYPE_REGISTER = 'register_url';
    const API_TYPE_UPLOAD = 'upload_url';
    const API_TYPE_DIVISION = 'division_url';
    const API_TYPE_PAYMENT = 'pay_url';
    const API_TYPE_PAYMENT_QUERY = 'pay_query_url';

    public $apiMethod;

    public $notifyUrl = '';

    public $requestUrl;

    public $postField;

    public $postFile = [];

    public $responseField;

    public $responseName;

    public $checkResponseSign = false;

    public $responseInfo;

    public $responseCode;

    public $curlHandle;

    public $response;

    public $responseData;

    public $needRequest;

    public $mustFillRequest;

    public $bizContent = [];

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function setUrl($apiType, $apiMethod = '', $notifyUrl = '')
    {
        switch ($apiType) {
            case self::API_TYPE_REGISTER:
                $this->requestUrl = $this->config['register_url'];
                break;
            case self::API_TYPE_DIVISION:
                $this->requestUrl = $this->config['division_url'];
                break;
            case  self::API_TYPE_UPLOAD:
                $this->requestUrl = $this->config['upload_url'];
                break;
            case self::API_TYPE_PAYMENT:
                $this->requestUrl = $this->config['pay_url'];
                break;
            case self::API_TYPE_PAYMENT_QUERY:
                $this->requestUrl = $this->config['pay_query_url'];
                break;
        }
        if ($apiMethod) {
            $this->setMethod($apiMethod);
        }
        if ($notifyUrl) {
            $this->setNotifyUrl($notifyUrl);
        }
    }

    public function setMethod($method)
    {
        $this->apiMethod = $method;
    }

    public function setNotifyUrl($url)
    {
        $this->notifyUrl = env('APP_URL') . $url;
    }

    public function setNeedRequest($needRequest)
    {
        $this->needRequest = $needRequest;
    }

    public function setMustFillRequest($mustFillRequest)
    {
        $this->mustFillRequest = $mustFillRequest;
    }

    public function setResponse($responseField, $responseName = '', $checkResponseSign = false)
    {
        $this->responseField = $responseField;
        $this->responseName = $responseName;
        $this->checkResponseSign = $checkResponseSign;
    }

    public function setPost($post)
    {
        $this->postField = $post;
    }

    public function getPostData($post)
    {
        //生成签名
        if (count($post) > 0) {
            $this->bizContent = json_encode($post, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
        } else {
            $post = new class
            {
            };
            $this->bizContent = json_encode($post, JSON_PRESERVE_ZERO_FRACTION);
        }

        $params = [
            'charset' => $this->config['charset'],
            'method' => $this->apiMethod,
            'partner_id' => $this->config['partner_id'],
            'sign_type' => 'RSA',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => $this->config['version'],
            'biz_content' => $this->bizContent
        ];

        Log::info('请求数据：');
        Log::info($this->bizContent);

        if ($this->notifyUrl) {
            $params['notify_url'] = $this->notifyUrl;
        }

        return $params;
    }

    public function setFile($file)
    {
        if ($file && file_exists($file)) {
            $this->postFile = ['picFile' => $file];
            //throw new Exception($file . ' 文件不存在', 500);
        } else {
            $this->postFile = [];
        }
    }

    public function send($need_sign = true, $check_data = true)
    {
        if ($need_sign) {
            $this->postField = $this->getPostData($this->postField);
            $sign = Util::sign($this->postField, $this->config);
            if ($sign) {
                $this->postField['sign'] = $sign;
            } else {
                throw new Exception('秘钥生成错误');
            }
        }

        $this->response = (new Request($this->requestUrl, $this->postField, $this->postFile))->send();

        return $this->receiveResponse($check_data);
    }

    protected function receiveResponse($check_data = true)
    {

        Log::info('Response: ' . $this->response);

        $responseJsonArray = json_decode($this->response, true, 512, JSON_PRESERVE_ZERO_FRACTION);

        if (empty($responseJsonArray)) {
            throw new Exception("解析错误，返回内容：\r\n" . $this->response);
        }

        if ($this->responseName) {
            $responseData = $responseJsonArray[$this->responseName];
        } else {
            $responseData = $responseJsonArray;
        }

        if ($this->checkResponseSign) {
            $data_json_str = json_encode($responseData, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE + JSON_PRESERVE_ZERO_FRACTION);
            //$data_json_str = json_encode($responseData, JSON_UNESCAPED_SLASHES);
            Log::info('返回验签数据：' . $data_json_str);
            if (!Util::sign_check($responseJsonArray['sign'], $data_json_str, $this->config)) {
                throw new Exception("验签失败，返回内容：\r\n" . $this->response);
            }
        }

        if ($check_data) {
            if (array_key_exists("code", $responseData) && "10000" != $responseData["code"]) {
                if (isset($responseData['sub_code']) && isset($responseData['sub_msg'])) {
                    throw new BusinessException($responseData);
                } else {
                    throw new Exception($responseData['msg'], $responseData["code"]);
                }
            }
        }

        $this->responseData = $responseData;

        return $this->responseData;
    }

}
