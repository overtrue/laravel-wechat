# EasyWeChat for Laravel

WeChat SDK [w7corp/easywechat](https://github.com/w7corp/easywechat) wrapper for Laravel.

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me-button-s.svg?raw=true)](https://github.com/sponsors/overtrue)

## Requirements

- overtrue/laravel-wechat:^7.0 -> Laravel >= 8.0
- overtrue/laravel-wechat:^6.0 -> Laravel/Lumen >= 7.0
- overtrue/laravel-wechat:^5.1 -> Laravel/Lumen >= 5.1

## Installation

```bash
composer require "overtrue/laravel-wechat"
```

## Config

1. publishe the config file to `config` directory：

  ```shell
  php artisan vendor:publish --provider="Overtrue\LaravelWeChat\ServiceProvider"
  ```

2. (Optional) You can add the alias to `config/app.php`:

  ```php
  'aliases' => [
      // ...
      'EasyWeChat' => Overtrue\LaravelWeChat\EasyWeChat::class,
  ],
  ```

3. Each module basically supports multiple accounts, the default name is `default`.

## Usage

Ignore the CSRF check for WeChat related routes:

```php
protected $except = [
    // ...
    'wechat',
];
```

The following is an example written to receive a server message:

Routes: 

```php
Route::any('/wechat', 'WeChatController@serve');
```

> **Note**
> 
> It must be `Route::any`, because the WeChat server validation is `GET` request, and when receiving user messages is `POST` request!

Then, let's create a controller `WeChatController`:

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

## OAuth middleware

If you're using middleware, just fill in `oauth.callback` with any value in `app/config/wechat.php` (because you won't need it :smile:).

1. Register the middleware to `app/Http/Kernel.php`:

  ```php
  protected $routeMiddleware = [
      // ...
      'easywechat.oauth' => \Overtrue\LaravelWeChat\Middleware\OAuthAuthenticate::class,
  ];
  ```

2. Add the middleware to oauth route:

  ```php
  //...
  Route::group(['middleware' => ['web', 'easywechat.oauth']], function () {
      Route::get('/user', function () {
          $user = session('easywechat.oauth_user.default'); // oauth user

          \dd($user);
      });
  });
  ```

  You can also set the config name and scopes as middleware paremeter:

  ```php
  // scopes
  Route::group(['middleware' => ['easywechat.oauth:snsapi_userinfo']], function () {
    // ...
  });

  // account name and scopes:
  Route::group(['middleware' => ['easywechat.oauth:default,snsapi_userinfo']], function () {
    // ...
  });
  ```

  The above route defines `/user` as requiring authorization from WeChat, so in the **callback or controller method** of this route, you can get the authorized user information from `session('easywechat.oauth_user.default')`.

## Mock Authorization

Sometimes we want to go real WeChat authorization process only after local development is completed online, which will reduce our development cost, then you need to do the following two steps.

1. Prepare mock authorization information:

  ```php
  use Illuminate\Support\Arr;
  use Overtrue\Socialite\User as SocialiteUser;

  $user = new SocialiteUser([
              'id' => 'mock-openid',
              'name' => 'overtrue',
              'nickname' => 'overtrue',
              'avatar' => 'http://example.com/avatars/overtrue.png',
              'email' => null,
              'original' => [],
              'provider' => 'WeChat',
          ]);
  ```

  > If your schema is only `snsapi_base`, you only need `openid`. 

2. Write the information to the session:

  ```php
  // Similarly, `default` can be replaced with your corresponding other configuration name
  session(['easywechat.oauth_user.default' => $user]); 
  ```
  
  > **Note**
  > 
  > Be sure to write before calling the OAuth middleware, for example, you can create a global middleware to do this, and just enable it in the development environment.


## Events

You can listen to the corresponding events and perform the corresponding actions when they occur.

- **OAuth authorized**: `Overtrue\LaravelWeChat\Events\WeChatUserAuthorized`

```php
// The event has the following properties
$event->user; // same as session('easywechat.oauth_user.default')
$event->isNewSession; // if it is a new session (true when first creating a session)
$event->account; // the current account used by the middleware, corresponding to the name of the configuration item in the configuration file
```


## Open Platform Support

You can apply the built-in `Overtrue\LaravelWeChat\Traits\HandleOpenPlatformServerEvents` to quickly complete the server-side validation for the open platform: 

*routes/web.php:*

```php
Route::any('/open-platform/server', OpenPlatformController::class);
```

*app/Http/Controllers/OpenPlatformController.php:*

```php
<?php

namespace App\Http\Controllers;

use Overtrue\LaravelWeChat\Traits\HandleOpenPlatformServerEvents;

class OpenPlatformController extends Controller
{
    public function __invoke(Application $application): \Psr\Http\Message\ResponseInterface
    {
        $app = app('easywechat.open-platform');
        
        return $this->handleServerEvents($app);
    }
}
```

> **Note**
> 
> By default, the following events will be triggered based on the push content of WeChat Open Platform, you can listen to the corresponding events and process them.

- **Authorized party successfully authorized**: `Overtrue\LaravelWeChat\Events\OpenPlatform\Authorized`
- **Authorized party updates authorization**: `Overtrue\LaravelWeChat\Events\OpenPlatform\AuthorizeUpdated`
- **Authorizer deauthorized**: `Overtrue\LaravelWeChat\Events\OpenPlatform\Unauthorized`
- **OpenPlatform Push VerifyTicket**: `Overtrue\LaravelWeChat\Events\OpenPlatform\VerifyTicketRefreshed`

```php
// Events have the following properties
$message = $event->payload; // Open Platform event notification content
```

After configuration `http://example.com/open-platform/server` is the authorized event receiving URL set for the Open Platform third party application.


For more SDK specific usage, please refer to: <https://www.easywechat.com>

## :heart: Sponsor me 

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me.svg?raw=true)](https://github.com/sponsors/overtrue)

If you like my project and want to support it, [click here :heart:](https://github.com/sponsors/overtrue)

## Project supported by JetBrains

Many thanks to Jetbrains for kindly providing a license for me to work on this and other open-source projects.

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/overtrue)

## License

MIT
