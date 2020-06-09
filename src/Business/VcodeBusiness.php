<?php

namespace Cann\Vcode\Business;

use Carbon\Carbon;
use Cann\Vcode\Models\Vcode;
use Cann\Vcode\Jobs\SendVcodeJob;
use Cann\Vcode\Helpers\ToolsHelper;
use Cann\Vcode\Notifications\VcodeNotification;

class VcodeBusiness
{
    // 发送短信验证码
    public static function sendVcode(string $channel, string $scene, string $target)
    {
        // 获取上次发送时间
        $lastSentAt = Vcode::where('channel', $channel)
            ->where('scene', $scene)
            ->where('target', $target)
            ->orderBy('created_at', 'desc')
            ->value('created_at');

        // 还需冷却 X 秒
        if ($lastSentAt && ($coolingTime = now()->diffInSeconds($lastSentAt, true)) <= config('vcode.interval')) {
            // return ToolsHelper::output('cooling_time', ['seconds' => config('vcode.interval') - $coolingTime]);
        }

        // 将未使用的验证码置为已废弃
        $where = [
            'channel' => $channel,
            'scene'   => $scene,
            'target'  => $target,
            'status'  => Vcode::STATUS_UNUSED,
        ];

        Vcode::where($where)->update(['status' => Vcode::STATUS_REVOKED]);

        // 生成验证码
        $vcode = self::generateVcode(config('vcode.vcode.length', 4));

        // 生产验证码对应Key
        $vcodeKey = \Str::random(36);

        // 创建发送记录
        $vcode = Vcode::create([
            'channel'    => $channel,
            'scene'      => $scene,
            'target'     => $target,
            'vcode_key'  => $vcodeKey,
            'vcode'      => $vcode,
            'status'     => Vcode::STATUS_UNUSED,
            'sent_at'    => date('Y-m-d H:i:s'),
            'expried_at' => now()->addMinutes(config('vcode.vcode.valid_minutes', 5)),
        ]);

        $channelClass = 'Cann\Vcode\Notifications\Channels\\' . \Str::studly($channel) . 'Channel';

        // 发送验证码
        if (config('vcode.queue.enable')) {
            \Notification::route('target', $target)->notify(new VcodeNotification($channelClass, $vcode));
        } else {
            \Notification::route('target', $target)->notifyNow(new VcodeNotification($channelClass, $vcode));
        }

        return ToolsHelper::output('sent_success', [
            'seconds'   => config('vcode.interval'),
            'vcode_key' => $vcodeKey,
        ]);
    }

    // 验证码验证
    public static function verifyVcode(string $vcodeKey, string $vcode)
    {
        // 万能验证码
        $universal = config('biz.universal_code');

        if ($universal && $vcode == $universal) {
            return true;
        }

        $rVcode = Vcode::where('vcode_key', $vcodeKey)->orderBy('id', 'desc')->first();

        // 无效的 vcodeKey
        if (! $rVcode) {
            return false;
        }

        // 已达最大尝试次数
        if ($rVcode->attempts >= config('vcode.vcode.max_attempts')) {
            return false;
        }

        // 尝试次数 +1
        $rVcode->increment('attempts');

        // 验证码不正确
        if ($rVcode->vcode != $vcode) {
            return false;
        }

        // 验证码不可用
        if ($rVcode->status != Vcode::STATUS_UNUSED) {
            return false;
        }

        // 验证码已过期
        if ($rVcode->expried_at <= date('Y-m-d H:i:s')) {
            return false;
        }

        // 验证码置为已使用
        $rVcode->update(['status' => Vcode::STATUS_USED]);

        return true;
    }

    // 生成短信验证码
    protected static function generateVcode(int $length)
    {
        return mt_rand(pow(10, $length - 1), pow(10, $length) - 1);
    }
}
