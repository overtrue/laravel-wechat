# laravel-wechat

微信 SDK for Laravel， 基于 [overtrue/wechat](https://github.com/overtrue/wechat)

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me-button-s.svg?raw=true)](https://github.com/sponsors/overtrue)

> 7.x 起不再默认支持 Lumen。

## 框架要求

- overtrue/laravel-wechat:^7.0 -> Laravel >= 8.0
- overtrue/laravel-wechat:^6.0 -> Laravel/Lumen >= 7.0
- overtrue/laravel-wechat:^5.1 -> Laravel/Lumen >= 5.1

## 安装

```bash
composer require "overtrue/laravel-wechat"
```

## 配置

1. 创建配置文件：

```shell
php artisan vendor:publish --provider="Overtrue\LaravelWeChat\ServiceProvider"
```

2. 可选，添加别名

```php
'aliases' => [
    // ...
    'EasyWeChat' => Overtrue\LaravelWeChat\EasyWeChat::class,
],
```

3. 每个模块基本都支持多账号，默认为 `default`。

## 使用

:rotating_light: 在中间件 `App\Http\Middleware\VerifyCsrfToken` 排除微信相关的路由，如：

```php
protected $except = [
    // ...
    'wechat',
];
```

下面以接收普通消息为例写一个例子。

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
    public function serve()
    {
        Log::info('request arrived.'); 

        $server = app('easywechat.official_account')->getServer();

        $server->with(function($message){
            return "欢迎关注 overtrue！";
        });

        return $server->serve();
    }
}
```

## OAuth 中间件

使用中间件的情况下 `app/config/wechat.php` 中的 `oauth.callback` 就随便填写吧(因为用不着了 :smile:)。

1. 在 `app/Http/Kernel.php` 中添加路由中间件：

```php
protected $routeMiddleware = [
    // ...
    'easywechat.oauth' => \Overtrue\LaravelWeChat\Middleware\OAuthAuthenticate::class,
];
```

2. 在路由中添加中间件：

```php
//...
Route::group(['middleware' => ['web', 'easywechat.oauth']], function () {
    Route::get('/user', function () {
        $user = session('easywechat.oauth_user.default'); // 拿到授权用户资料

        dd($user);
    });
});
```

中间件支持指定配置名称：`'easywechat.oauth:default'`，当然，你也可以在中间件参数指定当前的 `scopes`:

```php
Route::group(['middleware' => ['easywechat.oauth:snsapi_userinfo']], function () {
  // ...
});

// 或者指定账户的同时指定 scopes:
Route::group(['middleware' => ['easywechat.oauth:default,snsapi_userinfo']], function () {
  // ...
});
```

上面的路由定义了 `/user` 是需要微信授权的，那么在这条路由的**回调 或 控制器对应的方法里**， 你就可以从 `session('easywechat.oauth_user.default')` 拿到已经授权的用户信息了。

## 模拟授权

有时候我们希望在本地开发完成后线上才真实的走微信授权流程，这将减少我们的开发成本，那么你需要做以下两步：

1. 准备假资料：

> 以下字段在 scope 为 `snsapi_userinfo` 时尽可能配置齐全哦，当然，如果你的模式只是 `snsapi_base` 的话只需要 `openid` 就好了。

```php
use Illuminate\Support\Arr;
use Overtrue\Socialite\User as SocialiteUser;

$user = new SocialiteUser([
                'id' => Arr::get($user, 'openid'),
                'name' => Arr::get($user, 'nickname'),
                'nickname' => Arr::get($user, 'nickname'),
                'avatar' => Arr::get($user, 'headimgurl'),
                'email' => null,
                'original' => [],
                'provider' => 'WeChat',
            ]);

```

2. 将资料写入 session：

> 注意：一定要在 OAuth 中间件之前写入，比如你可以创建一个全局中间件来完成这件事儿，当然了，只在开发环境启用即可。

```php
session(['easywechat.oauth_user.default' => $user]); // 同理，`default` 可以更换为您对应的其它配置名
```

## 事件

> 你可以监听相应的事件，并对事件发生后执行相应的操作。

- OAuth 网页授权：`Overtrue\LaravelWeChat\Events\WeChatUserAuthorized`

```php
// 该事件有以下属性
$event->user; // 同 session('easywechat.oauth_user.default') 一样
$event->isNewSession; // 是不是新的会话（第一次创建 session 时为 true）
$event->account; // 当前中间件所使用的账号，对应在配置文件中的配置项名称
```


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



更多 SDK 的具体使用请参考：https://www.easywechat.com

## :heart: Sponsor me 

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me.svg?raw=true)](https://github.com/sponsors/overtrue)

如果你喜欢我的项目并想支持它，[点击这里 :heart:](https://github.com/sponsors/overtrue)

## Project supported by JetBrains

Many thanks to Jetbrains for kindly providing a license for me to work on this and other open-source projects.

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/overtrue)


## PHP 扩展包开发

> 想知道如何从零开始构建 PHP 扩展包？
>
> 请关注我的实战课程，我会在此课程中分享一些扩展开发经验 —— [《PHP 扩展包实战教程 - 从入门到发布》](https://learnku.com/courses/creating-package)

## License

MIT
