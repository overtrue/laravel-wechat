# laravel-wechat

> 注意：此版本为 3.x 版本，不兼容 2.x 与 1.x，与 [overtrue/wechat 3.x](https://github.com/overtrue/wechat) 同步

微信 SDK for Laravel 5 / Lumen， 基于 [overtrue/wechat](https://github.com/overtrue/wechat)

本项目只适用于，只有一个固定的账号，如果是开发微信公众号管理系统就不要使用了，直接用 [overtrue/wechat](https://github.com/overtrue/wechat) 更方便些。

> 交流QQ群：319502940

## 安装

1. 安装包文件

  ```shell
  composer require "overtrue/laravel-wechat:~3.0"
  ```

> 如果你用了 laravel-debugbar，请禁用或者关掉，否则这模块别想正常使用！！！

> 如果你用了 laravel-debugbar，请禁用或者关掉，否则这模块别想正常使用！！！

> 如果你用了 laravel-debugbar，请禁用或者关掉，否则这模块别想正常使用！！！

## 配置

### Laravel 应用

1. 注册 `ServiceProvider`:

  ```php
  Overtrue\LaravelWechat\ServiceProvider::class,
  ```

2. 创建配置文件：

  ```shell
  php artisan vendor:publish
  ```

3. 请修改应用根目录下的 `config/wechat.php` 中对应的项即可；

4. （可选）添加外观到 `config/app.php` 中的 `aliases` 部分:

  ```php
  'Wechat' => Overtrue\LaravelWechat\Facade::class,
  ```

### Lumen 应用

1. 在 `bootstrap/app.php` 中 82 行左右：

  ```php
  $app->register(Overtrue\LaravelWechat\ServiceProvider::class);
  ```

2. ENV 中支持以下配置：

```php
WECHAT_APPID
WECHAT_SECRET
WECHAT_TOKEN
WECHAT_AES_KEY

WECHAT_LOG_LEVEL
WECHAT_LOG_FILE

WECHAT_OAUTH_SCOPES
WECHAT_OAUTH_CALLBACK

WECHAT_PAYMENT_MERCHANT_ID
WECHAT_PAYMENT_KEY
WECHAT_PAYMENT_CERT_PATH
WECHAT_PAYMENT_KEY_PATH
WECHAT_PAYMENT_DEVICE_INFO
WECHAT_PAYMENT_SUB_APP_ID
WECHAT_PAYMENT_SUB_MERCHANT_ID
```

3. 如果你习惯使用 `config/wechat.php` 来配置的话，将 `vendor/overtrue/laravel-wechat/src/config.php` 拷贝到`app/config`目录下，并将文件名改成`wechat.php`。

## 使用

### Laravel <= 5.1

1. Laravel 5 起默认启用了 CSRF 中间件，因为微信的消息是 POST 过来，所以会触发 CSRF 检查导致无法正确响应消息，所以请去除默认的 CSRF 中间件，改成路由中间件。可以参考我的写法：[overtrue gist:Kernel.php](https://gist.github.com/overtrue/ff6cd3a4e869fbaf6c01#file-kernel-php-L31)
2. 5.1 里的 CSRF 已经带了可忽略部分url的功能，你可以参考：http://laravel.com/docs/master/routing#csrf-protection

### Laravel 5.2

Laravel 5.2 默认启用了 web 中间件，意味着 CSRF 会默认打开，有两种方案：

1. 在 CSRF 中间件里排除微信相关的路由
2. 关掉 CSRF 中间件（极不推荐）


下面以接收普通消息为例写一个例子：

> 假设您的域名为 `overtrue.me` 那么请登录微信公众平台 “开发者中心” 修改 “URL（服务器配置）” 为： `http://overtrue.me/wechat`。

路由：

```php
Route::any('/wechat', 'WechatController@serve');
```

> 注意：一定是 `Route::any`, 因为微信服务端认证的时候是 `GET`, 接收用户消息时是 `POST` ！

然后创建控制器 `WechatController`：

```php
<?php

namespace App\Http\Controllers;

use Log;

class WechatController extends Controller
{

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            return "欢迎关注 overtrue！";
        });

        Log::info('return response.');

        return $wechat->server->serve();
    }
}
```

> 上面例子里的 Log 是 Laravel 组件，所以它的日志不会写到 EasyWeChat 里的，建议把 wechat 的日志配置到 Laravel 同一个日志文件，便于调试。

### 我们有以下方式获取 SDK 的服务实例

##### 使用容器的自动注入

```php
<?php

namespace App\Http\Controllers;

use EasyWeChat\Foundation\Application;

class WechatController extends Controller
{

    public function demo(Application $wechat)
    {
        // $wechat 则为容器中 EasyWeChat\Foundation\Application 的实例
    }
}
```

##### 使用外观

在 `config/app.php` 中 `alias` 部分添加外观别名：

```php
'EasyWeChat' => Overtrue\LaravelWechat\Facade::class,
```

然后就可以在任何地方使用外观方式调用 SDK 对应的服务了：

```php
  $wechatServer = EasyWeChat::server(); // 服务端
  $wechatUser = EasyWeChat::user(); // 用户服务
  // ... 其它同理
```


## OAuth 中间件

使用中间件的情况下 `app/config/wechat.php` 中的 `oauth.callback` 就随便填写吧(因为用不着了 :smile:)。

1. 在 `app/Http/Kernel.php` 中添加路由中间件：

```php
protected $routeMiddleware = [
    // ...
    'wechat.oauth' => \Overtrue\LaravelWechat\Middleware\OAuthAuthenticate::class,
];
```

2. 在路由中添加中间件：

以 5.2 为例：

```php
//...
Route::group(['middleware' => ['web', 'wechat.oauth']], function () {
    Route::get('/user', function () {
        $user = session('wechat.oauth_user'); // 拿到授权用户资料

        dd($user);
    });
});
```
_如果你在用 5.1 上面没有 'web' 中间件_

上面的路由定义了 `/user` 是需要微信授权的，那么在这条路由的**回调 或 控制器对应的方法里**， 你就可以从 `session('wechat.oauth_user')` 拿到已经授权的用户信息了。


更多 SDK 的具体使用请参考：https://easywechat.org

## License

MIT
