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

        $channels = config('vcode.channels');

        unset($channels['default']);

        $request->validate([
            'channel' => 'nullable|in:' . implode(',', array_keys($channels)),
            'scene'   => 'required|in:' . implode(',', array_keys($channels[$channel]['scenes'])),
            $channels[$channel]['field'] => $channels[$channel]['validation'],
        ]);

        $response = VcodeBusiness::sendVcode($channel, $request->scene, $request->{$channels[$channel]['field']});

        return $response;
    }
}
