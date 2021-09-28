<?php

namespace Cann\Vcode\Business;

use Cache;
use Carbon\Carbon;
use Cann\Vcode\Models\Vcode;
use Cann\Vcode\Jobs\SendVcodeJob;
use Cann\Vcode\Helpers\ToolsHelper;
use Cann\Vcode\Notifications\VcodeNotification;
use Mews\Captcha\Captcha;

class VcodeBusiness
{
    // 发送短信验证码
    public static function sendVcode(
        string $channel,
        string $scene,
        string $target,
        string $captchaKey,
        string $captchaCode
    ) {
        // 钩子检测
        self::callHookFunc($channel, $scene, $target, '_hook_pre_send');

        // 冷却时间
        if ($coolingTime = self::validateCoolingTime($channel, $scene, $target)) {
            return ToolsHelper::output('cooling_time', ['seconds' => $coolingTime]);
        }

        // 验证图形验证码
        if (! self::validateCaptcha($captchaKey, $captchaCode)) {
            return ToolsHelper::output('captcha_invalid', ['captcha_api' => route('vcode-captcha')]);
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

        // 创建发送记录
        $vcode = Vcode::create([
            'channel'    => $channel,
            'scene'      => $scene,
            'target'     => $target,
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
            'seconds' => config('vcode.interval'),
        ]);
    }

    protected static function callHookFunc(string $channel, string $scene, string &$target, string $hookName)
    {
        $channelCnf = config('vcode.channels.' . $channel);

        $hookFunc = $channelCnf['scenes'][$scene][$hookName] ?? null;

        if ($hookFunc && is_callable($hookFunc)) {
            $hookFunc($target);
        }
    }

    // 验证冷却时间
    protected static function validateCoolingTime(string $channel, string $scene, string $target)
    {
        // 获取上次发送时间
        $lastSentAt = Vcode::where('channel', $channel)
            ->where('scene', $scene)
            ->where('target', $target)
            ->orderBy('created_at', 'desc')
            ->value('created_at');

        // 还需冷却 X 秒
        if ($lastSentAt && ($coolingTime = now()->diffInSeconds($lastSentAt, true)) <= config('vcode.interval')) {
            return config('vcode.interval') - $coolingTime;
        }

        return 0;
    }

    // 短信发送总数是否达到触发图形验证码阈值
    protected static function validateCaptcha(string $captchaKey, string $captchaCode)
    {
        $sendNum = Vcode::whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H') = '" . date('Y-m-d H') . "'")->count();

        // 未达到图形验证码阈值
        if ($sendNum < config('vcode.captcha.trigger_by_vcode_num_hourly')) {
            return true;
        }

        // 验证通过
        if (self::verifyCaptcha($captchaKey, $captchaCode)) {
            return true;
        }

        return false;
    }

    // 生成短信验证码
    protected static function generateVcode(int $length)
    {
        return mt_rand(pow(10, $length - 1), pow(10, $length) - 1);
    }

    // 验证码验证
    public static function verifyVcode(string $channel, string $scene, string $target, string $vcode)
    {
        // 万能验证码
        $universal = config('vcode.universal_code');

        if ($universal && $vcode == $universal) {
            return true;
        }

        if (! $channel || ! $scene || ! $target || ! $vcode) {
            return false;
        }

        if (! $rVcode = Vcode::where(compact('channel', 'scene', 'target', 'vcode'))->orderBy('id', 'desc')->first()) {
            return false;
        }

        // 已达最大尝试次数
        if (config('vcode.vcode.max_attempts') > 0 && $rVcode->attempts >= config('vcode.vcode.max_attempts')) {
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

    // 获取图形验证码
    public static function getCaptcha()
    {
        $captcha = self::mewsCaptcha()->create('vcode', true);

        return ToolsHelper::output('captcha_response', [
            'sensitive'   => $captcha['sensitive'],
            'captcha_key' => $captcha['key'],
            'captcha_img' => $captcha['img'],
        ]);
    }

    // 验证图形验证码
    protected static function verifyCaptcha(string $key, string $captcha)
    {
        if (! $key || ! $captcha) {
            return false;
        }

        $cacheKey = 'UsedCaptchaKey:' . $key;

        // 验证码错误
        if (! self::mewsCaptcha()->check_api($captcha, $key)) {
            return false;
        }

        return Cache::add($cacheKey, 1);
    }

    protected static function mewsCaptcha()
    {
        return new Captcha(
            app('Illuminate\Filesystem\Filesystem'),
            app('Illuminate\Contracts\Config\Repository'),
            app('Intervention\Image\ImageManager'),
            app('Illuminate\Session\Store'),
            app('Illuminate\Hashing\BcryptHasher'),
            app('Illuminate\Support\Str')
        );
    }
}
