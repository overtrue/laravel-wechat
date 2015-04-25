# laravel-wechat

微信 SDK for Laravel 5， 基于 [overtrue/wechat](https://github.com/overtrue/wechat)

本项目只适用于，只有一个固定的账号，如果是开发微信公众号管理系统就不要使用了，直接用 [overtrue/wechat](https://github.com/overtrue/wechat) 更方便些。

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
  php artisan vendor:publish --provider="Overtrue\LaravelWechat\ServiceProvider"
  ```

  然后请修改 `config/wechat.php` 中对应的项即可。


## 使用

> 注意：

> 1. Laravel 5 默认启用了 CRSF 中间件，因为微信的消息是 POST 过来，所以会触发 CRSF 检查导致无法正确响应消息，所以请去除默认的 CRSF 中间件，改成路由中间件。[默认启用的代码位置](https://github.com/laravel/laravel/blob/master/app/Http/Kernel.php#L18)

所有的Wechat对象都已经放到了容器中，直接从容器中取就好。

别名对应关系如下：

  'wechat.user'      => 'Overtrue\\Wechat\\User',
  'wechat.group'     => 'Overtrue\\Wechat\\Group',
  'wechat.auth'      => 'Overtrue\\Wechat\\Auth',
  'wechat.menu'      => 'Overtrue\\Wechat\\Menu',
  'wechat.menu.item' => 'Overtrue\\Wechat\\MenuItem',
  'wechat.js'        => 'Overtrue\\Wechat\\Js',
  'wechat.staff'     => 'Overtrue\\Wechat\\Staff',
  'wechat.store'     => 'Overtrue\\Wechat\\Store',
  'wechat.card'      => 'Overtrue\\Wechat\\Card',
  'wechat.qrcode'    => 'Overtrue\\Wechat\\QRCode',
  'wechat.url'       => 'Overtrue\\Wechat\\Url',
  'wechat.media'     => 'Overtrue\\Wechat\\Media',
  'wechat.image'     => 'Overtrue\\Wechat\\Image',

下面以接收普通消息为例写一个例子：

路由：

```php
Route::any('/wechat', 'WechatController@serve');
```

> 注意：一定是 `Route::any`, 因为微信服务端认证的时候是 `GET`, 接收用户消息时是 `POST` ！

这里假设您的域名为 `overtrue.me` 那么请登录微信公众平台 “开发者中心” 修改 “URL（服务器配置）” 为： `http://overtrue.me/wechat`。

然后创建控制器 `WechatController`：

```php
<?php namespace App\Http\Controllers;

use Log;

class WechatController extends Controller {

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        App::on('message', function($message){
            Log::info("收到来自'{$message['FromUserName']}'的消息：{$message['Content']}");
        });

        return Wechat::serve();
    }
}
```

> 注意：不要忘记在头部 `use Wechat` 哦，或者你就得用 `\Wechat` 咯。:smile:

### 从容器获取 `Wechat` 实例

```php
  $wechat = App::make('wechat');
  $wechat->on('message', ...);
```

更多使用请参考：https://github.com/overtrue/wechat/wiki/

## License

MIT
