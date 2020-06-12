# laravel-vcode

便捷的验证码发送工具，目前支持基于[easysms][https://github.com/overtrue/easy-sms]的短信验证码

### 安装

```
composer require cann/laravel-vcode
```

### 发布资源

```
php artisan vendor:publish --provider="Cann\Vcode\VcodeServiceProvider"
```

### 数据库迁移

```
php artisan migrate
```

### 参数配置

`config/easysms.php`: 短信服务商配置

`config/vcode.php`: 验证码相关配置

### 使用

#### 发送验证码

该工具提供了统一的接口进行验证码发送：

##### 请求

`POST`:`{host}/{prefix}/vcode`

#### 请求参数

| 字段 | 必填 | 类型 | 详细描述 |
| ---- | ---- | ---- | -------- |
| channel | 否 | STRING | 验证码发送渠道 <br/> `sms`: 短信验证码 (默认) <br/> `email`: 邮箱验证码 (暂不支持) |
| scene | 是 | STRING | 发送场景 <br/> `config/vcode.channels.{channel}.scenes` 中定义 <br/> 注：不同场景短信冷却时间不共享，验证码不通用 |
| mobile | 是 | STRING | 发送目标，字段名可在 `config/vcode.channels.{channel}.field` 修改 |
| captcha_key | 否 | STRING | 图形验证码 Key <br/> 注：当触发图形验证码时，该值必填 |
| captcha_code | 否 | STRING | 图形验证码 <br/> 注：当触发图形验证码时，该值必填 |

#### 响应参数

| 字段 | 类型 | 详细描述 |
| ---- | -----| ------- |
| code | INT | 响应码，可在 `config/vcode.responses` 修改 |
| message | STRING | 响应消息，可在 `config/vcode.responses` 修改 |
| data | ARRAY | 响应数据 |

#### 响应成功样例

```json
{
    "code": 0,
    "message": "发送成功",
    "data": {
        "seconds": 60
    }
}
```

or

```json
{
    "code": -1,
    "message": "你的动作太快了，请在 57 秒后重试",
    "data": {
        "seconds": 57
    }
}
```

or

```json
{
    "code": -101,
    "message": "请输入图形验证码",
    "data": {
        "captcha_api": "http://yocann.cn/api/captcha"
    }
}
```

#### 验证码校验

- 使用 Validator 验证，可在 `vcode` 参数上加上 `verify_code` 验证器进行验证，`verify_code` 会自动读取请求中的 `channel` `scene` `mobile` `vcode` 参数进行校验
- 使用 `verify_code(string $channel, string $scene, string $mobile, string $vcode)` 方法进行校验
