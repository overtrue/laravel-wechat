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
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use Overtrue\LaravelWeChat\Events\WeChatUserAuthorized;

/**
 * 仅适用于微信公众号, 企业微信的网页应用。
 */
class OAuthAuthenticate
{
    public function handle(
        Request $request,
        Closure $next,
        string $account = 'default',
        string $scope = null,
        ?string $type = 'service'
    ): mixed {
        // 保证兼容性参数处理
        $prefix = ('work' !== $type) ? 'official_account' : 'work';

        $sessionKey = \sprintf('easywechat.oauth_user.%s', $account);
        $name = \sprintf('easywechat.%s.%s', $prefix, $account);
        $config = config($name, []);
        $service = app($name);

        $scope = $scope ?: Arr::get($config, 'oauth.scopes', ['snsapi_base']);

        if (\is_string($scope)) {
            $scope = \array_map('trim', explode(',', $scope));
        }

        if (Session::has($sessionKey)) {
            event(new WeChatUserAuthorized(session($sessionKey), false, $account));

            return $next($request);
        }

        // 是否强制使用 HTTPS 跳转
        $enforceHttps = Arr::get($config, 'oauth.enforce_https', false);

        if ($request->has('code')) {
            session([$sessionKey => $service->getOAuth()->userFromCode($request->query('code'))]);

            event(new WeChatUserAuthorized(session($sessionKey), true, $account));

            return redirect()->to($this->getIntendUrl($request, $enforceHttps));
        }

        session()->forget($sessionKey);

        // 跳转到微信授权页
        return redirect()->away(
            $service->getOAuth()->scopes($scope)->redirect($this->getRedirectUrl($request, $enforceHttps))
        );
    }

    protected function getIntendUrl(Request $request, bool $https = false): string
    {
        $query = Arr::except($request->query(), ['code', 'state']);
        $url = $request->url();

        if ($https) {
            $url = $this->ensureHttpsScheme($url);
        }

        return $url.(empty($query) ? '' : '?'.http_build_query($query));
    }

    protected function getRedirectUrl(Request $request, bool $https = false): string
    {
        if (! $https) {
            return $request->fullUrl();
        }

        return $this->ensureHttpsScheme($request->fullUrl());
    }

    #[Pure]
    protected function ensureHttpsScheme(string $url): string
    {
        if (Str::startsWith($url, 'http://')) {
            $url = Str::replaceFirst('http', 'https', $url);
        }

        return $url;
    }
}
