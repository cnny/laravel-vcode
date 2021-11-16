<?php

namespace Cann\Vcode\Helpers;

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

        if (is_callable($config['message'])) {
            $message = $config['message']();
        }
        else {
            $message = $config['message'];
        }

        $response = [
            'code'    => $config['code'],
            'message' => self::_T($message, $data),
            'data'    => $data,
        ];

        return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // 记录第三方请求日志
    public static function logging(array $args, callable $callback)
    {
        // 当前时间
        $nowMs = microtime(true);

        // 请求流水号
        $requestSn = \Str::orderedUuid();

        // 记录请求报文
        \Log::channel('api_request')->info('req:' . $requestSn, [
            'req_args' => $args,
        ]);

        try {

            $response = call_user_func_array($callback, $args);

            // 记录响应报文（正常）
            \Log::channel('api_request')->info('resp:' . $requestSn, [
                'resp_body' => $response,
                'elapsed'   => round(microtime(true) - $nowMs, 6),
            ]);
        }

        catch (\Throwable $e) {

            \Log::channel('api_request')->info('resp_err:' . $requestSn, [
                'exception' => getFullException($e),
                'elapsed'   => round(microtime(true) - $nowMs, 6),
            ]);

            throw $e;
        }

        return $response;
    }
}
