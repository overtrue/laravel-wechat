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
  php artisan vendor:publish --provider="Overtrue\LaravelWechat\ServiceProvider"
  ```

  然后请修改 `config/wechat.php` 中对应的项即可。

4. 添加下面行到 `config/app.php` 的 `aliases` 部分：

  ```php
  'Wechat' => 'Overtrue\LaravelWechat\Facade',
  ```

## 使用

> 注意：你不需要在 `Wechat::make($config)` 了，我已经在拓展包里完成了这个动作，只要你在 `config/wechat.php` 里填写好配置就好了。

你有两种方式获取 `Wechat` 实例：

### 一、 使用外观（Facade）

由于我们已经添加了外观 `Wechat`，那么我们可以在控制器或者其它任何地方使用 `Wechat::方法名` 方式调用 SDK。

下面以接收普通消息为例写一个例子：

路由：

```php
Route::any('/wechat', 'WechatController@serve');
```
这里假设您的域名为 `overtrue.me` 那么请登录微信公众平台 “开发者中心” 修改 “URL（服务器配置）” 为： `http://overtrue.me/wechat`。

然后创建控制器 `WechatController`：

```php
<?php namespace App\Http\Controllers;

use Wechat;
use Log;

class WechatController extends Controller {

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        Wechat::on('message', function($message){
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
