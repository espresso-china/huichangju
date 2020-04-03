<?php
/**
 * Created by PhpStorm.
 * User: Tacker
 * Date: 16/12/20
 * Time: 12:37
 */

namespace App\Helpers;

use GuzzleHttp\Client;
use App\Exceptions\ApiException;
use App\Exceptions\HttpException;

class HttpHelper
{

    /**
     * POST方式获取接口数据
     * @param $url
     * @param mixed $post_data
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function post($url, $post_data = array())
    {
        $client = new Client(['timeout' => env('API_REQUEST_TIMEOUT', 10)]);
        if (is_array($post_data)) {
            $params = [
                'form_params' => $post_data
            ];
        } elseif (is_string($post_data)) {
            $params = [
                'body' => $post_data
            ];
        }
        \Log::info($url);
        \Log::info(http_build_query($params));
        try {
            $request = $client->request('POST', $url, $params);
            $code = $request->getStatusCode();
            $response = $request->getBody();
            if ($code == 200) {
                return $response;
            } else {
                $msg = '访问：' . $url . '，出现异常';
                throw new HttpException($msg, $code);
            }
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), 500, $url);
        }
    }

    /**
     * GET方式获取接口数据
     * @param $url
     * @param array $params
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function get($url, $params = array())
    {
        $client = new Client(['timeout' => env('API_REQUEST_TIMEOUT', 10)]);
        if (is_array($params)) {
            $url = $url . '?' . http_build_query($params);
        } elseif (is_string($params)) {
            $url = $url . '?' . $params;
        }
        \Log::info($url);
        \Log::info(http_build_query($params));
        try {
            $request = $client->request('GET', $url);
            $code = $request->getStatusCode();
            $response = $request->getBody();
            if ($code == 200) {
                return $response;
            } else {
                $msg = '访问：' . $url . '，出现异常';
                throw new HttpException($msg, $code);
            }
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), 500, $url);
        }
    }


}