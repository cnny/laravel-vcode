<?php

/**
 * 短信发送
 */
namespace Cann\Sms\Verification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Cann\Sms\Verification\Models\SmsVcode;
use Cann\Sms\Verification\Business\SmsBusiness;

class SendSmsVcodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $smsVcode;
    private $message;

    public function __construct(SmsVcode $smsVcode)
    {
        $this->smsVcode = $smsVcode;

        // 指定队列
        $this->queue = config('vcode.queue.channel', 'default');
    }

    public function handle()
    {
        SmsBusiness::sendVcodeRPC($this->smsVcode);
    }
}
