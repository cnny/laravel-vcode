<?php

namespace Cann\Vcode\Notifications;

use Cann\Vcode\Models\Vcode;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class VcodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $vcode;
    protected $channel;

    public function __construct($channel, Vcode $vcode)
    {
        $this->channel = $channel;
        $this->vcode   = $vcode;

        // 指定队列
        $this->queue = config('vcode.queue.channel', 'default');
    }

    public function via($notifiable)
    {
        return [$this->channel];
    }
}
