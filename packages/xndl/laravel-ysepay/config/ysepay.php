<?php

/*
 * This file is part of Commidity
 *
 * (c) Tacker <tacker.cn@gmail.com>
 *
 */

return [
    /**
     * Debug 模式，bool 值：true/false
     *
     * 当值为 false 时，所有的日志都不会记录
     */
    'debug' => true,

    'test' => false,

    'partner_id' => '10000447996', //商家账号

    'register_url' => 'https://register.ysepay.com:2443/register_gateway/gateway.do', //子商户注册接口地址

    'upload_url' => 'https://uploadApi.ysepay.com:2443/yspay-upload-service', //子商户资质文件上传地址

    'division_url' => 'https://commonapi.ysepay.com/gateway.do', //分账地址

    'charset' => 'utf-8', //编码

    'version' => '3.0', //版本

    'pfx_path' => '', //私钥
    'pfx_password' => '', //密码
    'business_gate_cert_path' => '', //公钥


    /**
     * 日志配置
     *
     * level: 日志级别，可选为：debug/info/notice/warning/error/critical/alert/emergency
     * file：日志文件位置(绝对路径!!!)，要求可写权限
     */
    'log' => [
        'level' => env('YSEPAY_LOG_LEVEL', 'debug'),
        'file' => env('YSEPAY_LOG_FILE', storage_path('logs/ysepay.log')),
    ],

];
