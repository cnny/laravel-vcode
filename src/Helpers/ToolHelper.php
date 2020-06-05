<?php

namespace Cann\Sms\Verification\Helpers;

class ToolsHelper
{
    // 替换字符串中的变量
    public static function _T(string $message, array $vars = [])
    {
        if (! $vars) {
            return $message;
        }

        $keys = $values = [];

        foreach ($vars as $key => $value) {

            $keys[] = '{' . $key . '}';

            if (! is_array($value)) {
                $values[] = htmlspecialchars($value);
            }

            else {
                $values[] = json_encode($value);
            }
        }

        return str_replace($keys, $values, $message);
    }

    public static function output(string $type, array $data = [])
    {
        $config = config('vcode.responses.' . $type);

        $message = self::_T($config['message'], $data);

        $response = [
            'code'    => $config['code'],
            'message' => $message,
            'data'    => $data,
        ];

        return $response;
    }
}
