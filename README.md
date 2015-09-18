# laravel-wechat

> 注意：此版本为 2.x 版本，不兼容 1.x，已经移除外观，与 [overtrue/wechat 2.x](https://github.com/overtrue/wechat) 同步

> 1.x 的配置文件里面的项目为驼峰，2.x 系列已经改为下划线，请参考: [src/config.php](https://github.com/overtrue/laravel-wechat/blob/master/src/config.php)

微信 SDK for Laravel 5 / Lumen， 基于 [overtrue/wechat](https://github.com/overtrue/wechat)

本项目只适用于，只有一个固定的账号，如果是开发微信公众号管理系统就不要使用了，直接用 [overtrue/wechat](https://github.com/overtrue/wechat) 更方便些。

> 交流QQ群：319502940

## 安装

1. 安装包文件

  ```shell
  composer require "overtrue/laravel-wechat:2.1.*"
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

2. 在 ENV 中配置以下选项：

```php
WECHAT_USE_ALIAS=false
WECHAT_APPID=xxx
WECHAT_SECRET=xxx
WECHAT_TOKEN=xxx
WECHAT_ENCODING_KEY=xxx
```
3. 如果你习惯使用 `config/wechat.php` 来配置的话，请记得在 `bootstrap/app.php` 中19行以后添加：

```php
$app->configure('wechat');
```

## 使用

> 注意：

> 1. Laravel 5 默认启用了 CSRF 中间件，因为微信的消息是 POST 过来，所以会触发 CRSF 检查导致无法正确响应消息，所以请去除默认的 CRSF 中间件，改成路由中间件。可以参考我的写法：[overtrue gist:Kernel.php](https://gist.github.com/overtrue/ff6cd3a4e869fbaf6c01#file-kernel-php-L31)
> 5.1 里的 SCRF 已经带了可忽略部分url的功能，你可以参考：http://laravel.com/docs/master/routing#csrf-protection

所有的Wechat对象都已经放到了容器中，直接从容器中取就好。

别名对应关系如下：

    'wechat.server'    => 'Overtrue\Wechat\Server',
    'wechat.user'      => 'Overtrue\Wechat\User',
    'wechat.group'     => 'Overtrue\Wechat\Group',
    'wechat.auth'      => 'Overtrue\Wechat\Auth',
    'wechat.menu'      => 'Overtrue\Wechat\Menu',
    'wechat.menu.item' => 'Overtrue\Wechat\MenuItem',
    'wechat.js'        => 'Overtrue\Wechat\Js',
    'wechat.staff'     => 'Overtrue\Wechat\Staff',
    'wechat.store'     => 'Overtrue\Wechat\Store',
    'wechat.card'      => 'Overtrue\Wechat\Card',
    'wechat.qrcode'    => 'Overtrue\Wechat\QRCode',
    'wechat.url'       => 'Overtrue\Wechat\Url',
    'wechat.media'     => 'Overtrue\Wechat\Media',
    'wechat.image'     => 'Overtrue\Wechat\Image',
    'wechat.notice'     => 'Overtrue\Wechat\Notice',
    'wechat.media'     => 'Overtrue\Wechat\Media',

下面以接收普通消息为例写一个例子：

> 假设您的域名为 `overtrue.me` 那么请登录微信公众平台 “开发者中心” 修改 “URL（服务器配置）” 为： `http://overtrue.me/wechat`。

路由：

```php
Route::any('/wechat', 'WechatController@serve');
```

> 注意：一定是 `Route::any`, 因为微信服务端认证的时候是 `GET`, 接收用户消息时是 `POST` ！

然后创建控制器 `WechatController`：

```php
<?php namespace App\Http\Controllers;

use Overtrue\Wechat\Server;
use Log;

class WechatController extends Controller {

    /**
     * 处理微信的请求消息
     *
     * @param Overtrue\Wechat\Server $server
     *
     * @return string
     */
    public function serve(Server $server)
    {
        $server->on('message', function($message){
            return "欢迎关注 overtrue！";
        });

        return $server->serve(); // 或者 return $server;
    }
}
```

> 注意：不要忘记在头部 `use` 哦，或者你就得用 `\Overtrue\Wechat\Server` 全称咯。:smile:

### 我们有三种方式获取 SDK 的服务实例

##### 使用容器的自动注入

```php
<?php namespace App\Http\Controllers;

use Overtrue\Wechat\Auth;

class WechatController extends Controller {

    public function demo(Auth $auth)
    {
        // $auth 则为容器中 Overtrue\Wechat\Auth 的实例
    }
}
```

##### 使用别名/类名从容器获取对应实例

上面已经列出了所有可用的别名对应关系，你可以使用别名或者类名获取对应的实例：

```php
  $wechatServer = App::make('wechat.server'); // 服务端
  $wechatUser = App::make('wechat.user'); // 用户服务
  或者：
  $wechatUser = App::make('Overtrue\Wechat\User'); // 用户服务
  // ... 其它同理
```

#### 使用外观 `Wechat`

```php
$wechatServer = Wechat::server();
$wechatUser = Wechat::user();
//... 其它同理
```

更多使用请参考：https://github.com/overtrue/wechat/wiki/

## License

MIT
