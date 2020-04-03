<?php
/**
 * User: Tacker
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/3/29
 * Time: 20:14
 */

namespace Xndl\YsePay\YsePay\Http;

use Xndl\YsePay\YsePay\Util\Log;
use Xndl\YsePay\YsePay\Exceptions\Exception;

class Request
{


    public $requestUrl;

    public $postField;

    public $postFile;

    public $responseInfo;

    public $responseCode;

    public $curlHandle;

    public $response;


    public function __construct($url, $post, $file = [])
    {
        $this->requestUrl = $url;
        $this->postField = $post;
        $this->postFile = $file;
    }

    public function getHeader()
    {
        $header = array(
            'Content-Type: multipart/form-data',
        );
        return $header;
    }

    public function setPost()
    {
        $options = [];

        if ($this->postFile) {
            if (class_exists('\CURLFile')) {    //PHP版本>=5.5
                $options[CURLOPT_SAFE_UPLOAD] = true;
                foreach ($this->postFile as $k => $v) {
                    $this->postField[$k] = new \CURLFile(realpath($v));
                }
            } else {
                if (defined('CURLOPT_SAFE_UPLOAD')) {   //PHP版本<=5.5
                    $options[CURLOPT_SAFE_UPLOAD] = false;
                }
                foreach ($this->postFile as $k => $v) {
                    $this->postField[$k] = '@' . $v;
                }
            }
            ksort($this->postField);
            $options[CURLOPT_POSTFIELDS] = $this->postField;
        } else {
            $options[CURLOPT_POSTFIELDS] = http_build_query($this->postField);
        }

        Log::info(json_encode($this->postField));

        // set options
        return @curl_setopt_array($this->curlHandle, $options);
    }


    public function getResponse()
    {
        $this->response;
    }

    public function send()
    {
        Log::info($this->requestUrl);

        $this->curlHandle = curl_init($this->requestUrl);
        curl_setopt($this->curlHandle, CURLOPT_POST, 1);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, 1);

        if (empty($this->postFile)) {
            curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($this->curlHandle, CURLOPT_USERAGENT, "YsePay PHPSDK v1.0");
            curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, 30);
            curl_setopt($this->curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        }

        $this->setPost();

        $this->response = curl_exec($this->curlHandle);
        $this->responseCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        //$this->responseInfo = curl_getinfo($this->curlHandle);

        if ($this->response == '') {
            throw new Exception("请求错误" . $this->responseCode);
        }

        curl_close($this->curlHandle);
        //$this->responseInfo = null;
        return $this->response;
    }
}
