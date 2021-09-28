<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cann\Vcode\Extensions\EasySms\Gateways;

use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Sms\V20210111\SmsClient;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use Cann\Vcode\Helpers\ToolsHelper;

/**
 * Class QcloudV3Gateway
 *
 * @see https://cloud.tencent.com/document/product/382/43195
 */
class QcloudV3Gateway extends Gateway
{
    const ENDPOINT_URL = 'https://sms.tencentcloudapi.com';

    public function getName()
    {
        return 'qcloud_v3';
    }

    /**
     * @param \Overtrue\EasySms\Contracts\PhoneNumberInterface $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface     $message
     * @param \Overtrue\EasySms\Support\Config                 $config
     *
     * @return array
     *
     * @throws \Overtrue\EasySms\Exceptions\GatewayErrorException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $cred = new Credential(
            $config->get('secret_id'),
            $config->get('secret_key'),
        );

        $httpProfile = new HttpProfile;
        $httpProfile->setEndpoint('sms.tencentcloudapi.com');

        $clientProfile = new ClientProfile;
        $clientProfile->setHttpProfile($httpProfile);

        $client = new SmsClient($cred, $config->get('region'), $clientProfile);

        // 目标手机号
        $phoneNumber = '+' . ($to->getIDDCode() ?: '86') . $to->getNumber();

        // 消息内容
        $data = $message->getData($this);

        $params = [
            'PhoneNumberSet'   => [$phoneNumber],
            'SmsSdkAppId'      => $config->get('sdk_app_id'),
            'SignName'         => $config->get('sign_name'),
            'TemplateId'       => $message->getTemplate($this),
            'TemplateParamSet' => $data ? array_values($data) : [],
        ];

        $callback = function ($action, $params) use ($client) {
            $req = new SendSmsRequest;
            $req->fromJsonString(json_encode($params));
            return $client->{$action}($req);
        };

        // 记录请求响应日志
        $resp = ToolsHelper::logging(['SendSms', $params], $callback);

        $body = $resp->serialize();

        if (! isset($body) || ! $body) {
            throwx('TencentCloud 响应内容为空');
        }

        return $body;
    }
}
