<?php

namespace Cann\Sms\Verification\Business;

use Carbon\Carbon;
use Overtrue\EasySms\EasySms;
use Cann\Sms\Verification\Models\Vcode;
use Cann\Sms\Verification\Jobs\SendVcodeJob;
use Cann\Sms\Verification\Helpers\ToolsHelper;

class VcodeBusiness
{
    // 发送短信验证码
    public static function sendVcode(string $channel, string $target)
    {
        // 获取上次发送时间
        $lastSentAt = Vcode::where('channel', $channel)
            ->where('target', $target)
            ->orderBy('created_at', 'desc')
            ->value('created_at');

        // 还需冷却 X 秒
        if ($lastSentAt && ($coolingTime = now()->diffInSeconds($lastSentAt, true)) <= config('vcode.interval')) {
            return ToolsHelper::output('cooling_time', ['seconds' => config('vcode.interval') - $coolingTime]);
        }

        // 生成验证码
        $vcode = self::generateVcode(config('vcode.vcode.length', 4));

        // 创建发送记录
        $vcode = Vcode::create([
            'channel' => $channel,
            'target'  => $target,
            'vcode'   => $vcode,
            'status'  => Vcode::STATUS_WAITING,
        ]);

        // 发送验证码
        if (config('vcode.queue.enable')) {
            SendVcodeJob::dispatch($vcode);
        } else {
            SendVcodeJob::dispatchNow($vcode);
        }

        return ToolsHelper::output('sent_success', ['seconds' => config('vcode.interval')]);
    }

    public static function sendVcodeRPC(Vcode $vcode)
    {
        $easySms = new EasySms(config('easysms'));

        $content = config('vcode.content');

        if (! $content['template'] && $content['content']) {

            $content['data'] = json_decode(ToolsHelper::_T(json_encode($content['data']), ['code' => $vcode->vcode]), true);

            $content['content'] = ToolsHelper::_T($content['content'], $content['data']);

            unset($content['template']);
            unset($content['data']);
        }

        else {
            unset($content['content']);
        }

        try {

            $result = $easySms->send($vcode->mobile, $content);

            $vcode->update([
                'status'     => Vcode::STATUS_SUCCEED,
                'content'    => $content,
                'result'     => $result,
                'sent_at'    => date('Y-m-d H:i:s'),
                'expried_at' => now()->addMinutes(config('vcode.vcode.valid_minutes')),
            ]);

        } catch (\Throwable $e) {

            $vcode->update([
                'status'     => Vcode::STATUS_FAILED,
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
        $sms = Vcode::where('mobile', $mobile)
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
