<?php

namespace Cann\Vcode\Notifications\Channels;

use Overtrue\EasySms\PhoneNumber;
use Cann\Vcode\Helpers\ToolsHelper;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        // 发送场景
        $scene = config('vcode.channels.sms.scenes.' . $notification->vcode->scene);

        // 国际区码
        $IDDCode = null;

        if (strpos($notifiable->routes['target'], '-') !== false) {
            [$IDDCode, $mobile] = explode('-', $notifiable->routes['target']);
        }
        else {
            $mobile = $notifiable->routes['target'];
        }

        $to = new PhoneNumber($mobile, $IDDCode);

        app('easysms')->send($to, [

            // 短信文本
            'content' => function ($gateway) use ($scene, $notification, $to) {

                $content = $scene[$gateway->getName()]['template'] ?? null;
                $content = is_callable($content) ? $content($gateway, $to) : $content;

                return ToolsHelper::_T($content, ['vcode' => $notification->vcode->vcode]);
            },

            // 短信模板
            'template' => function ($gateway) use ($scene, $notification, $to) {

                $template = $scene[$gateway->getName()]['template'] ?? null;

                return is_callable($template) ? $template($gateway, $to) : $template;
            },

            // 短信变量
            'data' => function ($gateway) use ($scene, $notification, $to) {

                $data = $scene[$gateway->getName()]['data'] ?? [];

                $data = is_callable($data) ? $data($gateway, $to) : $data;

                foreach ($data as &$one) {
                    $one = ToolsHelper::_T($one, ['vcode' => $notification->vcode->vcode]);
                }

                return $data;
            }

        ]);
    }
}
