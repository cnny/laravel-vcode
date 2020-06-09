<?php

namespace Cann\Sms\Verification\Models;

use Illuminate\Database\Eloquent\Model;

class Vcode extends Model
{
    protected $guarded = [];

    protected $table = 'sms_vcodes';

    protected $casts = [
        'content'    => 'array',
        'result'     => 'array',
        'failed_msg' => 'array',
    ];

    const
        STATUS_WAITING = 0, // 待发送
        STATUS_SUCCEED = 1, // 成功
        STATUS_FAILED  = 2; // 失败
}
