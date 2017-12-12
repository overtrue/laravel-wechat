# laravel-wechat

‼️ 注意：此版本为 4.x 版本，不兼容 3.x，与 [overtrue/wechat 4.x](https://github.com/overtrue/wechat) 同步

‼️ 如果你用的 3.x 版本，请从这里查看文档 https://github.com/overtrue/laravel-wechat/tree/3.1.10

微信 SDK for Laravel 5 / Lumen， 基于 [overtrue/wechat](https://github.com/overtrue/wechat)

> 交流QQ群：319502940

<p align="center">
  <br>
  <b>创造不息，交付不止</b>
  <br>
  <a href="https://www.yousails.com">
    <img src="https://yousails.com/banners/brand.png" width=350>
  </a>
</p>

## 框架要求

Laravel/Lumen >= 5.1

## 安装

```shell
composer require "overtrue/laravel-wechat:~4.0"
```

## 配置

### Laravel 应用

1. 在 `config/app.php` 注册 ServiceProvider 和 Facade (Laravel 5.5 无需手动注册)

```php
'providers' => [
    // ...
    Overtrue\LaravelWeChat\ServiceProvider::class,
],
'aliases' => [
    // ...
    'EasyWeChat' => Overtrue\LaravelWeChat\Facade::class,
],
```

2. 创建配置文件：

```shell
php artisan vendor:publish --provider="Overtrue\LaravelWeChat\ServiceProvider"
```

3. 修改应用根目录下的 `config/wechat.php` 中对应的参数即可。

### Lumen 应用

1. 在 `bootstrap/app.php` 中 82 行左右：

```php
$app->register(Overtrue\LaravelWeChat\ServiceProvider::class);
```

2. 如果你习惯使用 `config/wechat.php` 来配置的话，将 `vendor/overtrue/laravel-wechat/src/config.php` 拷贝到`app/config`目录下，并将文件名改成`wechat.php`。

## 使用

:rotating_light: 在中间件 `App\Http\Middleware\VerifyCsrfToken` 排除微信相关的路由，如：

```php
protected $except = [
    // ...
    'wechat',
];
```

下面以接收普通消息为例写一个例子：

> 假设您的域名为 `overtrue.me` 那么请登录微信公众平台 “开发者中心” 修改 “URL（服务器配置）” 为： `http://overtrue.me/wechat`。

路由：

```php
Route::any('/wechat', 'WeChatController@serve');
```

> 注意：一定是 `Route::any`, 因为微信服务端认证的时候是 `GET`, 接收用户消息时是 `POST` ！

然后创建控制器 `WeChatController`：

```php
<?php

namespace App\Http\Controllers;

use Log;

class WeChatController extends Controller
{

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $app = app('wechat.official_account');
        $app->server->push(function($message){
            return "欢迎关注 overtrue！";
        });

        return $app->server->serve();
    }
}
```

> 上面例子里的 Log 是 Laravel 组件，所以它的日志不会写到 EasyWeChat 里的，建议把 wechat 的日志配置到 Laravel 同一个日志文件，便于调试。

### 我们有以下方式获取 SDK 的服务实例

##### 使用容器的自动注入

以公众号为例：

```php
<?php

namespace App\Http\Controllers;

use EasyWeChat\OfficialAccount\Application;

class WechatController extends Controller
{

    public function demo(Application $officialAccount)
    {
        // $officialAccount 则为容器中 EasyWeChat\OfficialAccount\Application 的实例
    }
}
```

##### 使用外观

```php
  $officialAccount = EasyWeChat::officialAccount(); // 公众号
  $work = EasyWeChat::work(); // 企业微信
  $payment = EasyWeChat::payment(); // 微信支付
  $openPlatform = EasyWeChat::openPlatform(); // 开放平台
  $miniProgram = EasyWeChat::miniProgram(); // 小程序

```

## OAuth 中间件

使用中间件的情况下 `app/config/wechat.php` 中的 `oauth.callback` 就随便填写吧(因为用不着了 :smile:)。

1. 在 `app/Http/Kernel.php` 中添加路由中间件：

```php
protected $routeMiddleware = [
    // ...
    'wechat.oauth' => \Overtrue\LaravelWeChat\Middleware\OAuthAuthenticate::class,
];
```

2. 在路由中添加中间件：

```php
//...
Route::group(['middleware' => ['web', 'wechat.oauth']], function () {
    Route::get('/user', function () {
        $user = session('wechat.oauth_user'); // 拿到授权用户资料

        dd($user);
    });
});
```

当然，你也可以在中间件参数指定当前的 `scopes`:

```php
Route::group(['middleware' => ['web', 'wechat.oauth:snsapi_userinfo']], function () {
  // ...
});
```

上面的路由定义了 `/user` 是需要微信授权的，那么在这条路由的**回调 或 控制器对应的方法里**， 你就可以从 `session('wechat.oauth_user')` 拿到已经授权的用户信息了。

## 开放平台路由支持

在配置文件 `route` 处取消注释即可启用。

```php
'open_platform' => [
    'uri' => 'serve',
    'action' => Overtrue\LaravelWeChat\Controllers\OpenPlatformController::class,
    'attributes' => [
        'prefix' => 'open-platform',
        'middleware' => null,
    ],
],
```

Tips: 默认的控制器会根据微信开放平台的推送内容触发如下事件，你可以监听相应的事件并进行处理：

- 授权方成功授权：`Overtrue\LaravelWeChat\Events\OpenPlatform\Authorized`
- 授权方更新授权：`Overtrue\LaravelWeChat\Events\OpenPlatform\UpdateAuthorized`
- 授权方取消授权：`Overtrue\LaravelWeChat\Events\OpenPlatform\Unauthorized`
- 开放平台推送 VerifyTicket：`Overtrue\LaravelWeChat\Events\OpenPlatform\VerifyTicketRefreshed`

```php
// 事件有如下属性
$message = $event->payload; // 开放平台事件通知内容
```

配置后 `http://example.com/open-platform/serve` 则为开放平台第三方应用设置的授权事件接收 URL。

## 模拟授权

有时候我们希望在本地开发完成后线上才真实的走微信授权流程，这将减少我们的开发成本，那么你需要做以下两步：

1.  在 config/wechat.php 中将：'enable_mock' 启用，不论你是用 `.env` 文件里 `WECHAT_ENABLE_MOCK=true` 或者其它什么方法都可以。
2.  在 config/wechat.php 中配置 `mock_user` 为微信的模拟的用户资料:

```php
/*
 * 开发模式下的免授权模拟授权用户资料
 *
 * 当 enable_mock 为 true 则会启用模拟微信授权，用于开发时使用，开发完成请删除或者改为 false 即可
 */
'enable_mock' => env('WECHAT_ENABLE_MOCK', true),
'mock_user' => [
    'openid' => 'odh7zsgI75iT8FRh0fGlSojc9PWM',
    // 以下字段为 scope 为 snsapi_userinfo 时需要
    'nickname' => 'overtrue',
    'sex' => '1',
    'province' => '北京',
    'city' => '北京',
    'country' => '中国',
    'headimgurl' => 'http://wx.qlogo.cn/mmopen/C2rEUskXQiblFYMUl9O0G05Q6pKibg7V1WpHX6CIQaic824apriabJw4r6EWxziaSt5BATrlbx1GVzwW2qjUCqtYpDvIJLjKgP1ug/0',
],
```

以上字段在 scope 为 `snsapi_userinfo` 时尽可能配置齐全哦，当然，如果你的模式只是 `snsapi_base` 的话只需要 `openid` 就好了。

## 事件

> 你可以监听相应的事件，并对事件发生后执行相应的操作。

- OAuth 网页授权：`Overtrue\LaravelWeChat\Events\WeChatUserAuthorized`

```php
// 该事件有两个属性
$event->user; // 同 session('wechat.oauth_user') 一样
$event->isNewSession; // 是不是新的会话（第一次创建 session 时为 true）
```

更多 SDK 的具体使用请参考：https://easywechat.com

## License

MIT
