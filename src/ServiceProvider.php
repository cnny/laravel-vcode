<?php

namespace Cann\Sms\Verification;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // 验证中文手机号
        \Validator::extend('zh_mobile', function ($attribute, $value) {
            return preg_match('/^(\+?0?86\-?)?1[3-9]{1}\d{9}$/', $value);
        }, '你输入的是一个无效的手机号码');
    }

    public function register()
    {
        // do nothing
    }

    protected static function initConfig()
    {
        // do nothing
    }

    protected function registerPublishing()
    {
        // do nothing
    }
}
