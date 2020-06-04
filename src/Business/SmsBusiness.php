<?php

namespace Cann\Sms\Verification\Business;

use Carbon\Carbon;
use Cann\Sms\Verification\Models\Sms;
use Cann\Sms\Verification\Jobs\SendSms;

class SmsBusiness
{
    // 发送短信验证码
    public static function sendVerifyCode(string $mobile)
    {
        // 获取上次发送时间
        $cooledAt = Sms::where('mobile', $mobile)
            ->orderBy('sent_at', 'desc')
            ->value('cooled_at');

        // 还需冷却X秒
        $remainSecs = now()->diffInSeconds($cooledAt, false);

        if ($remainSecs > 0) {
            return [
                'is_send' => 0,
                'time'    => $remainSecs,
            ];
        }

        // 生成验证码冷却时间为5分钟
        $vcode = \Cache::remember('SmsVerifyCode:'. $mobile, config('biz.sms_code_ttl', 300), function () {
            return mt_rand(100000, 999999);
        });

        // 生成验证码 记录本次发送
        $sms = Sms::create([
            'mobile' => $mobile,
            'type'   => 'verifyCode',
            'vcode'  => mt_rand(100000, 999999),
        ]);

        // 发送短信
        SendSms::dispatch($sms);

        return [
            'is_send' => 1,
            'time'    => config('biz.sms_code_cd', 60),
        ];
    }

    public static function sendSmsVcode(Sms $sms, $message)
    {
        // 发送手机短信验证码
        try {
            app('easysms')->send($sms->mobile, $message);
        } catch (\Throwable $e) {

        }
    }

    // 验证手机短信验证码
    public static function verifySmsCode(string $mobile, string $vcode)
    {
        // 万能验证码
        $uniVcode = config('biz.universal_code');

        if ($uniVcode && $vcode == $uniVcode) {
            return true;
        }

        // 获取最近一条短信
        $sms = Sms::where('mobile', $mobile)
            ->orderBy('sent_at', 'desc')
            ->value('vcode');

        if (! $sms->vcode || $sms->vcode != $vcode) {
            throws('验证码错误||请重新输入正确的短信验证码');
        }

        // 是否已过失效时间
        if (Carbon::parse($sms->expired_at)->isPast()) {
            throws('验证码错误||验证码已失效');
        }

        // 置为失效
         $sms->update([
             'expired_at'=> now()->subSeconds(1),
         ]);

        return false;
    }

}
