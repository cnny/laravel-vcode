<?php

namespace Cann\Sms\Verification\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Cann\Sms\Verification\Business\SmsBusiness;

class SmsController extends Controller
{
    // 发送手机短信验证码
    public function send(Request $request)
    {
        $request->validate([
            'mobile' => 'required|zh_mobile',
        ]);

        $result = SmsBusiness::sendVerifyCode($request->mobile);

        return ok(__FUNCTION__, $result);
    }
}
