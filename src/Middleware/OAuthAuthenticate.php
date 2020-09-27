<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWeChat\Middleware;

use Closure;
use http\Env\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Overtrue\LaravelWeChat\Events\WeChatUserAuthorized;

/**
 * Class OAuthAuthenticate: 微信公众号, 企业微信的网页应用。
 */
class OAuthAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $account
     * @param string|null              $scope
     * @param string|null              $type : service(服务号), subscription(订阅号), work(企业微信)
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $account = 'default', $scope = null, $type = 'service')
    {
        //保证兼容性
        $class = ('work' !== $type) ? 'wechat' : 'work';
        $prefix = ('work' !== $type) ? 'official_account' : 'work';
        $sessionKey = \sprintf('%s.oauth_user.%s', $class, $account);
        $service = \sprintf('wechat.%s.%s', $prefix, $account);
        $config = config($service, []);
        $officialAccount = app($service);

        $scope = $scope ?: Arr::get($config, 'oauth.scopes', ['snsapi_base']);

        if (is_string($scope)) {
            $scope = array_map('trim', explode(',', $scope));
        }

        if (Session::has($sessionKey)) {
            event(new WeChatUserAuthorized(session($sessionKey), false, $account));
            return $next($request);
        }

        // 是否强制使用 HTTPS 跳转
        $enforceHttps = Arr::get($config, 'oauth.enforce_https', false);

        if ($request->has('code')) {
            session([$sessionKey => $officialAccount->oauth->user()]);

            event(new WeChatUserAuthorized(session($sessionKey), true, $account));

            return redirect()->to($this->getTargetUrl($request, $enforceHttps));
        }

        session()->forget($sessionKey);

        // 跳转到微信授权页
        return redirect()->away(
            $officialAccount->oauth->scopes($scope)->redirect($this->getRedirectUrl($request, $enforceHttps))->getTargetUrl()
        );
    }

    /**
     * Build the target business url.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $https
     * @return string
     */
    protected function getTargetUrl($request, $https = false)
    {
        $queries = Arr::except($request->query(), ['code', 'state']);
        $url = $request->url();

        if ($https && Str::startsWith($url, 'http://')) {
            $url = Str::replaceFirst('http', 'https', $url);
        }

        return $url . (empty($queries) ? '' : '?' . http_build_query($queries));
    }

    /**
     * generate the redirect url
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $https
     * @return string
     */
    protected function getRedirectUrl($request, $https = false)
    {
        if (!$https) {
            return $request->fullUrl();
        }

        return Str::startsWith($request->fullUrl(), 'http://')
            ? Str::replaceFirst('http', 'https', $request->fullUrl())
            : $request->fullUrl();
    }
}
