<?php

namespace Cann\Vcode\Notifications\Channels;

use Overtrue\EasySms\PhoneNumber;
use Cann\Vcode\Helpers\ToolsHelper;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        $vcode = $notification->vcode;

        $content = config('vcode.channels.sms.scenes.' . $vcode->scene);

        // 替换模板中的验证码
        $content = json_decode(ToolsHelper::_T(json_encode($content), ['vcode' => $vcode->vcode]), true);

        // 国际区码
        $IDDCode = null;

        if (strpos($notifiable->routes['target'], '-') !== false) {
            [$IDDCode, $mobile] = explode('-', $notifiable->routes['target']);
        }
        else {
            $mobile = $notifiable->routes['target'];
        }

        $phoneNumber = new PhoneNumber($mobile, $IDDCode);

        app('easysms')->send($phoneNumber, [
            'content' => function ($gateway) use ($content) {
                $content = $content[$gateway->getName()]['content'] ?? '';
                return $content ?: null;
            },
            'template' => function ($gateway) use ($content) {
                $template = $content[$gateway->getName()]['template'] ?? '';
                return  $template ?: null;
            },
            'data' => function ($gateway) use ($content) {
                return $content[$gateway->getName()]['data'] ?? [];
            }
        ]);
    }
}
