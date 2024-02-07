<?php

// easy-sms 配置
// @see https://github.com/overtrue/easy-sms
return [

    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [

        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => [
            'qcloud',
        ],
    ],

    // 可用的网关配置
    'gateways' => [

        'errorlog' => [
            'file' => storage_path('logs/easy-sms.log'),
        ],

        'qcloud' => [
            'secret_id'  => env('SMS_QCLOUD_SECRET_ID'),
            'secret_key' => env('SMS_QCLOUD_SECRET_KEY'),
            'sdk_app_id' => env('SMS_QCLOUD_APP_ID'),
            'sign_name'  => env('SMS_SIGN_NAME'),
            'region'     => env('SMS_QCLOUD_REGION'),
        ],

        'yunpian' => [
            'api_key' => env('SMS_YUNPIAN_API_KEY'),
        ],

        //...
    ],
];
