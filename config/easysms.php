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
            //...
        ],
    ],

    // 可用的网关配置
    'gateways' => [

        'errorlog' => [
            'file' => '/tmp/easy-sms.log',
        ],

        'qcloud' => [
            'sdk_app_id' => 'appidxxxxxxxxxxxxxxxx',
            'app_key'    => 'appkeyxxxxxxxxxxxxxxx',
            'sign_name'  => 'signxxxxxxxxxxxxxxxxx',
        ],

        'yunpian' => [
            'api_key' => '824f0ff2f71cab52936axxxxxxxxxx',
        ],

        //...
    ],
];
