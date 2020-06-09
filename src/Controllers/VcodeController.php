<?php

namespace Cann\Sms\Verification\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Cann\Sms\Verification\Business\SmsBusiness;

class VcodeController extends Controller
{
    // 发送手机短信验证码
    public function send(Request $request)
    {
        $config = config('vcode.channel.' . $request->channel);

        $request->validate([
            'channel'        => 'required',
            $config['field'] => $config['validation'],
        ]);

        $response = VcodeBusiness::sendVcode($request->channel, $request->{$config['field']});

        return $response;
    }
}
