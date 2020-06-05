<?php

namespace Cann\Sms\Verification\Business;

use Carbon\Carbon;
use Overtrue\EasySms\EasySms;
use Cann\Sms\Verification\Models\SmsVcode;
use Cann\Sms\Verification\Jobs\SendSmsVcodeJob;
use Cann\Sms\Verification\Helpers\ToolsHelper;

class VcodeBusiness
{
    // 发送短信验证码
    public static function sendVcode(string $mobile)
    {
        // 获取上次发送时间
        $lastSentAt = SmsVcode::where('mobile', $mobile)
            ->orderBy('created_at', 'desc')
            ->value('created_at');

        // 还需冷却 X 秒
        if ($lastSentAt && ($coolingTime = now()->diffInSeconds($lastSentAt, true)) <= config('vcode.interval')) {
            return ToolsHelper::output('cooling_time', ['seconds' => config('vcode.interval') - $coolingTime]);
        }

        // 生成验证码
        $vcode = self::generateVcode(config('vcode.vcode.length', 4));

        // 创建发送记录
        $smsVcode = SmsVcode::create([
            'mobile' => $mobile,
            'vcode'  => $vcode,
            'status' => SmsVcode::STATUS_WAITING,
        ]);

        // 发送验证码
        if (config('vcode.queue.enable')) {
            SendSmsVcodeJob::dispatch($smsVcode);
        } else {
            SendSmsVcodeJob::dispatchNow($smsVcode);
        }

        return ToolsHelper::output('sent_success', ['seconds' => config('vcode.interval')]);
    }

    public static function sendVcodeRPC(SmsVcode $smsVcode)
    {
        $easySms = new EasySms(config('easysms'));

        $content = config('vcode.content');

        if (! $content['template'] && $content['content']) {

            $content['data'] = json_decode(ToolsHelper::_T(json_encode($content['data']), ['code' => $smsVcode->vcode]), true);

            $content['content'] = ToolsHelper::_T($content['content'], $content['data']);

            unset($content['template']);
            unset($content['data']);
        }

        else {
            unset($content['content']);
        }

        try {

            $result = $easySms->send($smsVcode->mobile, $content);

            $smsVcode->update([
                'status'     => SmsVcode::STATUS_SUCCEED,
                'content'    => $content,
                'result'     => $result,
                'sent_at'    => date('Y-m-d H:i:s'),
                'expried_at' => now()->addMinutes(config('vcode.vcode.valid_minutes')),
            ]);

        } catch (\Throwable $e) {

            $smsVcode->update([
                'status'     => SmsVcode::STATUS_FAILED,
                'content'    => $content,
                'failed_msg' => $e->getExceptions(),
            ]);

            throw $e;
        }
    }

    // 验证手机短信验证码
    public static function verifyVcode(string $mobile, string $vcode)
    {
        // 万能验证码
        $uniVcode = config('biz.universal_code');

        if ($uniVcode && $vcode == $uniVcode) {
            return true;
        }

        // 获取最近一条短信
        $sms = SmsVcode::where('mobile', $mobile)
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

    // 生成短信验证码
    protected static function generateVcode(int $length)
    {
        return mt_rand(pow(10, $length), pow(10, $length + 1) - 1);
    }
}
