<?php
/**
 * Created by PhpStorm.
 * User: Tacker
 * Date: 16/12/20
 * Time: 12:31
 */

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use App\Http\Resources\Resources;

class ResultHelper
{
    const SUCCESS_CODE = 0;
    const ERROR_SYS_CODE = 500;
    const ERROR_NOT_FOUND_CODE = 404;
    const ERROR_VALIDATOR_CODE = 400;
    const REDIRECT_CODE = 302;

    const TYPE_IS_ERROR = 'error';//错误结果
    const TYPE_IS_RESULT = 'result';//正常结果
    const TYPE_IS_REDIRECT = 'redirect'; //页面跳转

    /**
     * 没有找到记录
     * @param $msg
     * @param null $data
     * @param int $code
     * @return JsonResponse
     */
    public static function json_none($msg, $data = null, $code = 404)
    {
        return self::json_result($msg, $data, $code, self::TYPE_IS_ERROR);
    }

    /**
     * json规范格式Ajax返回结果
     * @param string $msg
     * @param mixed $data
     * @param int $code
     * @param string $type
     * @return JsonResponse
     * @Author Tacker
     */
    public static function json_result($msg, $data = null, $code = self::SUCCESS_CODE, $type = null)
    {
        $result = array(
            'type' => is_null($type) ? self::TYPE_IS_RESULT : $type,
            'msg' => $msg,
            'code' => $code,
            'data' => $data
        );
        return response()->json($result);
    }

    /**
     * 出现错误
     * @param $msg
     * @param null $data
     * @param int $code
     * @return JsonResponse
     */
    public static function json_error($msg, $data = null, $code = 500)
    {
        return self::json_result($msg, $data, $code, self::TYPE_IS_ERROR);
    }

    public static function validator_error($validator, $msg = '输入数据验证失败')
    {
        return self::json_error($msg, $validator->errors(), self::ERROR_VALIDATOR_CODE);
    }


    /**
     * 跳转地址JSON数据
     * @param string $msg
     * @param mixed $url
     * @return JsonResponse
     * @Author Tacker
     */
    public static function json_jump($msg, $url, $code = 302)
    {
        return self::json_result($msg, $url, $code, self::TYPE_IS_REDIRECT);
    }

    /**
     * 返回
     * @param string $msg
     * @param bool $success
     * @param null $route
     * @return $this
     */
    public static function back($msg = '', $success = true, $route = null)
    {
        if ($route) {
            return redirect()->to($route)->with($success ? 'success_message' : 'error_message', $msg)->withInput();
        } else {
            return redirect()->back()->with($success ? 'success_message' : 'error_message', $msg)->withInput();
        }
    }

    /**
     * 返回 API 资源
     * @param $data
     * @param array $params
     * @param string $msg
     * @param int $code
     * @return Resources
     */
    public static function resources($data, $params = [], $msg = 'success', $code = self::SUCCESS_CODE)
    {
        return (new Resources($data))->withData($params)->withMsg($msg)->withCode($code);
    }

    public static function resources_error($msg, $code = self::ERROR_SYS_CODE, $data = [])
    {
        return self::resources($data, [], $msg, $code);
    }

}