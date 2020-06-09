<?php

return [

    // 请求间隔（秒）
    'interval' => 60,

    // 万能验证码
    'universal_code' => '',

    // 验证码配置
    'vcode' => [

        // 长度
        'length' => 4,

        // 有效期（分）
        'valid_minutes' => 5,

        // 最大尝试次数，超过该数值验证码自动失效，0或负数则不启用
        'max_attempts' => 3,
    ],

    'channels' => [

        'default' => 'sms',

        'sms' => [

            'field' => 'mobile',

            'validation' => 'required|zh_mobile',

            'scenes' => [

                'scene1' => [

                    'qcloud' => [

                        'content'  => '您的验证码为：{vcode}，若非本人操作，请勿泄露。',
                        'template' => '',
                        'data' => [
                            'code' => '{vcode}'
                        ],
                    ],
                ]
            ],
        ],
    ],

    // 图形验证码
    'captcha' => [

        // 是否启用图形验证码
        'enable' => true,

        // 每小时若发送到指定 {trigger_by_vcode_num_hourly} 次数，需要返回图片验证码验证
        'trigger_by_vcode_num_hourly' => 10000,

        'length' => 4,
    ],

    // 队列
    'queue' => [

        // 是否启用队列
        'enable'  => true,

        // 队列渠道
        'channel' => 'default',
    ],

    // 路由
    'route' => [
        'prefix'     => '/api',
        'middleware' => ['api'],
    ],

    // 提示信息
    'responses' => [

        // 发送成功
        'sent_success' => [
            'code'    => 0,
            'message' => '发送成功',
        ],

        // 冷却时间未到
        'cooling_time' => [
            'code'    => -1,
            'message' => '你的动作太快了，请在 {seconds} 秒后重试',
        ],
    ],
];
