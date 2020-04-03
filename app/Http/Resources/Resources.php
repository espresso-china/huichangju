<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/1/22
 * Time: 10:36 AM
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class Resources extends ResourceCollection
{

    protected $result;
    protected $params = [];
    protected $msg = '数据拉取成功';
    protected $code = 0;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge([
            'code' => $this->code,
            'msg' => $this->msg,
            'data' => $this->collection
        ], $this->params);
        //return parent::toArray($request);
    }

    public function withData($params)
    {
        $this->params = $params;
        return $this;
    }

    public function withMsg($msg)
    {
        $this->msg = $msg;
        return $this;
    }

    public function withCode($code)
    {
        $this->code = $code;
        return $this;
    }
}