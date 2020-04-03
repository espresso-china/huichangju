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

    'fee' => 0.003, //手续费比例

    'partner_id' => 'fumeiswkj20190515', //商家账号
    'partner_name' => '长沙复美生物科技有限公司', //商家名称
    'org_merchant_no' => '6551000032', //机构号
    'sub_org_merchant_no' => '6551000033',//子机构号

    'register_url' => 'https://register.ysepay.com:2443/register_gateway/gateway.do', //子商户注册接口地址
    'register_notify_url' => PHP_SAPI === 'cli' ? '' : '/ysepay/register/notify',

    'upload_url' => 'https://uploadApi.ysepay.com:2443/yspay-upload-service?method=upload', //子商户资质文件上传地址

    'division_url' => 'https://commonapi.ysepay.com/gateway.do', //分账地址
    'division_notify_url' => PHP_SAPI === 'cli' ? '' : '/ysepay/division/notify',

    //'pay_url' => 'https://mertest.ysepay.com/openapi_gateway/gateway.do', //测试
    'pay_url' => 'https://openapi.ysepay.com/gateway.do', //正式
    'pay_notify_url' => PHP_SAPI === 'cli' ? '' : '/ysepay/payment/notify',

    'pay_query_url' => 'https://search.ysepay.com/gateway.do', //支付结果查询

    //设置未付款交易的超时时间，一旦超时，该笔交易就会自动被关闭。
    //取值范围：1m～15d。
    //m - 分钟，h - 小时，d - 天。
    //该参数数值不接受小数点，如1.5h，可转换为90m。
    //默认传3d
    'pay_timeout_express' => '1d',

    'charset' => 'utf-8', //编码

    'version' => '3.0', //版本

    'pfx_path' => storage_path('cert/prod/fumei.pfx'), //私钥
    'pfx_password' => 'ys123456', //私钥密码
    'business_gate_cert_path' => storage_path('cert/prod/businessgate.cer'), //公钥
    'business_code' => '3010002', //业务代码

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
