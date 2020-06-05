<?php

use Illuminate\Routing\Router;

Route::group([
    'prefix'        => config('vcode.route.prefix'),
    'middleware'    => config('vcode.route.middleware'),
    'namespace'     => 'Cann\Sms\Verification\Controllers',
], function (Router $router) {

    $router->post('/sms/vcode', 'SmsController@send');

});
