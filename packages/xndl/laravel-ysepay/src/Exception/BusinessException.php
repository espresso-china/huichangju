<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2020/1/8
 * Time: 3:05 PM
 */

namespace Xndl\YsePay\Exception;

use Throwable;

class BusinessException extends \Exception
{
    protected $response = null;

    public function __construct($responseData, Throwable $previous = null)
    {
        $this->response = $responseData;

        $message = $responseData['sub_msg'];
        $code = is_numeric($responseData["sub_code"]) ? $responseData["sub_code"] : $responseData["code"];

        parent::__construct($message, $code, $previous);
    }

    public function getResponse()
    {
        return $this->response;
    }
}
