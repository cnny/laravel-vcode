<?php

namespace Cann\Vcode\Models;

use Illuminate\Database\Eloquent\Model;

class Vcode extends Model
{
    protected $guarded = [];

    protected $table = 'vcodes';

    protected $casts = [
        'content'    => 'array',
        'result'     => 'array',
        'failed_msg' => 'array',
    ];

    const
        STATUS_UNUSED  = 0, // 未使用
        STATUS_USED    = 1, // 已使用
        STATUS_REVOKED = 2; // 已撤销
}
