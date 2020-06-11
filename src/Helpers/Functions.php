<?php

if (! function_exists('verify_vcode')) {

    function verify_vcode(string $channel, string $scene, string $target, string $vcode)
    {
        \Cann\Vcode\Business\VcodeBusiness::verifyVcode($channel, $scene, $target, $vcode);
    }
}
