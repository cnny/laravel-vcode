<?php

namespace Cann\Vcode\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Cann\Vcode\Business\VcodeBusiness;

class VcodeController extends Controller
{
    // 发送手机短信验证码
    public function send(Request $request)
    {
        // 发送渠道
        $channel = $request->channel ?: config('vcode.channels.default');

        if (! $channelCnf = config('vcode.channels.' . $channel)) {
            throw new \Exception('Invalid Channel');
        }

        $request->validate([
            'channel'            => 'nullable|string',
            'scene'              => 'required|in:' . implode(',', array_keys($channelCnf['scenes'])),
            $channelCnf['field'] => $channelCnf['validation'],
            'captcha_kwargs'     => 'nullable|array',
        ]);

        $captchaKwargs = $request->captcha_kwargs ?? [];
        $captchaKwargs['ip'] = $request->ip();

        $response = VcodeBusiness::sendVcode(
            $channel,
            $request->scene,
            $request->{$channelCnf['field']},
            $captchaKwargs
        );

        return $response;
    }

    // 获取图形验证码
    public function captcha(Request $request)
    {
        return VcodeBusiness::getCaptcha();
    }
}
