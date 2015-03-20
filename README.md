# laravel-wechat

微信 SDK for Laravel 5， 基于 [overtrue/wechat](https://github.com/overtrue/wechat)

## 安装

1. 安装包文件
```shell
composer require "overtrue/laravel-wechat:dev-master"
```

2. 添加 `ServiceProvider` 到您项目 `config/app.php` 中的 `providers` 部分:

```php
'Overtrue\LaravelWechat\ServiceProvider',
```

3. 创建配置文件:

```shell
php artisan vendor:publish --provider="Overtrue\LaravelWechat\ServiceProvider" --tag="config"
```

然后请修改 `config/wechat.php` 中对应的项即可。

4. 添加下面行到 `config/app.php` 的 `aliases` 部分：

```php
'Wechat' => 'Overtrue\LaravelWechat\Facade',
```

## 使用


由于我们已经添加了外观 `Wechat`，那么我们可以在控制器或者其它任何地方使用 `Wechat::xxx` 方式调用 SDK。

下面以接收普通消息为例写一个例子：

路由：

```php
Route::any('/wechat', 'WechatController@serve');
```
这里假设您的域名为 `overtrue.me` 那么请登录微信公众平台 “开发者中心” 修改 “URL（服务器配置）” 为： `http://overtrue.me/wechat`。

然后创建控制器 `WechatController`：

```php
<?php namespace App\Http\Controllers;

class WechatController extends Controller {

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        Wechat::on('message', function($message){
            \Log::info("收到来自'{$message['FromUserName']}'的消息：{$message['Content']}");
        });

        return Wechat::serve();
    }
}
```

更多使用请参考：https://github.com/overtrue/wechat/wiki/

## License

MIT