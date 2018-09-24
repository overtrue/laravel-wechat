# laravel-wechat

微信 SDK for Laravel 5 / Lumen， 基于 [overtrue/wechat](https://github.com/overtrue/wechat)

> 注意：此版本为 4.x 版本，不兼容 3.x，与 [overtrue/wechat 4.x](https://github.com/overtrue/wechat) 同步
>
> 如果你用的 3.x 版本，请从这里查看文档 https://github.com/overtrue/laravel-wechat/tree/3.1.10
> 
> Laravel 5.6 以上不支持 3.x 请使用 4.0 以上版本。
> 
> 交流QQ群：319502940


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

4. 每个模块基本都支持多账号，默认为 `default`。

### Lumen 应用

1. 在 `bootstrap/app.php` 中 82 行左右：

```php
$app->register(Overtrue\LaravelWeChat\ServiceProvider::class);
```

2. 如果你习惯使用 `config/wechat.php` 来配置的话，将 `vendor/overtrue/laravel-wechat/src/config.php` 拷贝到`项目根目录/config`目录下，并将文件名改成`wechat.php`。

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

##### 使用外观

```php
  $officialAccount = EasyWeChat::officialAccount(); // 公众号
  $work = EasyWeChat::work(); // 企业微信
  $payment = EasyWeChat::payment(); // 微信支付
  $openPlatform = EasyWeChat::openPlatform(); // 开放平台
  $miniProgram = EasyWeChat::miniProgram(); // 小程序
  
  // 均支持传入配置账号名称
  EasyWeChat::officialAccount('foo'); // `foo` 为配置文件中的名称，默认为 `default`
  //...
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
        $user = session('wechat.oauth_user.default'); // 拿到授权用户资料

        dd($user);
    });
});
```

中间件支持指定配置名称：`'wechat.oauth:default'`，当然，你也可以在中间件参数指定当前的 `scopes`:

```php
Route::group(['middleware' => ['wechat.oauth:snsapi_userinfo']], function () {
  // ...
});

// 或者指定账户的同时指定 scopes:
Route::group(['middleware' => ['wechat.oauth:default,snsapi_userinfo']], function () {
  // ...
});
```

上面的路由定义了 `/user` 是需要微信授权的，那么在这条路由的**回调 或 控制器对应的方法里**， 你就可以从 `session('wechat.oauth_user.default')` 拿到已经授权的用户信息了。

## 模拟授权

有时候我们希望在本地开发完成后线上才真实的走微信授权流程，这将减少我们的开发成本，那么你需要做以下两步：

1. 准备假资料：

> 以下字段在 scope 为 `snsapi_userinfo` 时尽可能配置齐全哦，当然，如果你的模式只是 `snsapi_base` 的话只需要 `openid` 就好了。

```php
use Overtrue\Socialite\User as SocialiteUser;

$user = new SocialiteUser([
                'id' => array_get($user, 'openid'),
                'name' => array_get($user, 'nickname'),
                'nickname' => array_get($user, 'nickname'),
                'avatar' => array_get($user, 'headimgurl'),
                'email' => null,
                'original' => [],
                'provider' => 'WeChat',
            ]);

```

2. 将资料写入 session：

> 注意：一定要在 OAuth 中间件之前写入，比如你可以创建一个全局中间件来完成这件事儿，当然了，只在开发环境启用即可。

```php
session(['wechat.oauth_user.default' => $user]); // 同理，`default` 可以更换为您对应的其它配置名
```

## 事件

> 你可以监听相应的事件，并对事件发生后执行相应的操作。

- OAuth 网页授权：`Overtrue\LaravelWeChat\Events\WeChatUserAuthorized`

```php
// 该事件有以下属性
$event->user; // 同 session('wechat.oauth_user.default') 一样
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



更多 SDK 的具体使用请参考：https://easywechat.com

## License

MIT
