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

        // 短信验证码
        'sms' => [

            // 手机号字段名
            'field' => 'mobile',

            // 手机号验证规则
            'validation' => 'required|zh_mobile',

            // 发送场景
            'scenes' => [

                // 场景1
                'scene1' => [

                    // 对发送目标的处理
                    '_hook_set_target' => function (string &$target) {
                        // ...
                    },

                    // 发送前的钩子检测
                    '_hook_pre_send' => function (string &$target) {
                        // ...
                    },

                    // 验证前的勾子检测
                    '_hook_pre_verify' => function (string &$target) {
                        // ...
                    },

                    // 短信服务商模板
                    'qcloud_v3' => [
                        'template' => function ($gateway, $to) {
                            return $to->getIDDCode() == '86' ? env('SMS_QCLOUD_VCODE_TPL') : env('SMS_QCLOUD_VCODE_TPL_INTL');
                        },
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

        // 图形验证码配置
        'config' => [
            'length'    => 5,     // 验证码长度
            'width'     => 120,   // 图片宽度
            'height'    => 36,    // 图片高度
            'quality'   => 90,    // 图片质量
            'sensitive' => false, // 是否启用大小写敏感
            'angle'     => 12,    // 角度
            'sharpen'   => 10,    // 锐化
            'blur'      => 2,     // 模糊
            'invert'    => false, // 反色
            'contrast'  => -5,    // 对比度
        ],
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

    // 接口 response
    'responses' => [

        // 发送成功
        'sent_success' => [
            'code'    => 0,
            'message' => '发送成功',
        ],

        // 冷却时间未到
        // 注：{seconds} 为变量，会在实际响应时替换
        'cooling_time' => [
            'code'    => -1,
            'message' => '你的动作太快了，请在 {seconds} 秒后重试',
        ],

        // 输入图形验证码
        'captcha_invalid' => [
            'code'    => -101,
            'message' => '请输入图形验证码',
        ],

        // 图形验证码
        'captcha_response' => [
            'code'    => 0,
            'message' => '图形验证码获取成功',
        ],
    ],
];
