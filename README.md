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
  'Wechat' => 'Overtrue\LaravelWechat\Facade',
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

3. 如果你习惯使用 `config/wechat.php` 来配置的话，请记得在 `bootstrap/app.php` 中19行以后添加：

```php
$app->configure('wechat');
```

## 使用

> 注意：

> 1. Laravel 5 默认启用了 CSRF 中间件，因为微信的消息是 POST 过来，所以会触发 CSRF 检查导致无法正确响应消息，所以请去除默认的 CSRF 中间件，改成路由中间件。可以参考我的写法：[overtrue gist:Kernel.php](https://gist.github.com/overtrue/ff6cd3a4e869fbaf6c01#file-kernel-php-L31)
> 5.1 里的 CSRF 已经带了可忽略部分url的功能，你可以参考：http://laravel.com/docs/master/routing#csrf-protection


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
        Log::info('request arrived.');

        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            return "欢迎关注 overtrue！";
        });

        Log::info('return response.');

        return $server->serve();
    }
}
```

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


更多 SDK 的具体使用请参考：https://easywechat.org

## License

MIT
