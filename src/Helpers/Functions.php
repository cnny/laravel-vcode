<?php

if (! function_exists('verify_vcode')) {

    function verify_vcode(string $scene, string $target, string $vcode, string $channel = '')
    {
        $channel = $channel ?: config('vcode.channels.default');

        return \Cann\Vcode\Business\VcodeBusiness::verifyVcode($channel, $scene, $target, $vcode);
    }
}
