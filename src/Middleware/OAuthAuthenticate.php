<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWechat\Middleware;

use Closure;
use Event;
use Log;
use Overtrue\LaravelWechat\Events\WeChatUserAuthorized;

/**
 * Class OAuthAuthenticate.
 */
class OAuthAuthenticate
{
    /**
     * Use Service Container would be much artisan.
     */
    private $wechat;

    /**
     * Inject the wechat service.
     */
    public function __construct($wechat)
    {
        $this->wechat = $wechat;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $scopes
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $scopes = null)
    {
        $isNewSession = false;
        $onlyRedirectInWeChatBrowser = config('wechat.oauth.only_wechat_browser', false);

        if ($onlyRedirectInWeChatBrowser && !$this->isWeChatBrowser($request)) {
            if (config('debug')) {
                Log::debug('[not wechat browser] skip wechat oauth redirect.');
            }

            return $next($request);
        }

        $scopes = $scopes ?: config('wechat.oauth.scopes', ['snsapi_base']);

        if (is_string($scopes)) {
            $scopes = array_map('trim', explode(',', $scopes));
        }

        if (!session('wechat.oauth_user') || $this->needReauth($scopes)) {
            if ($request->has('code')) {
                session(['wechat.oauth_user' => $this->wechat->oauth->user()]);
                $isNewSession = true;

                Event::fire(new WeChatUserAuthorized(session('wechat.oauth_user'), $isNewSession));

                return redirect()->to($this->getTargetUrl($request));
            }

            session()->forget('wechat.oauth_user');

            return $this->wechat->oauth->scopes($scopes)->redirect($request->fullUrl());
        }

        Event::fire(new WeChatUserAuthorized(session('wechat.oauth_user'), $isNewSession));

        return $next($request);
    }

    /**
     * Build the target business url.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function getTargetUrl($request)
    {
        $queries = array_except($request->query(), ['code', 'state']);

        return $request->url().(empty($queries) ? '' : '?'.http_build_query($queries));
    }

    /**
     * Is different scopes.
     *
     * @param array $scopes
     *
     * @return bool
     */
    protected function needReauth($scopes)
    {
        return session('wechat.oauth_user.original.scope') == 'snsapi_base' && in_array('snsapi_userinfo', $scopes);
    }

    /**
     * Detect current user agent type.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function isWeChatBrowser($request)
    {
        return strpos($request->header('user_agent'), 'MicroMessenger') !== false;
    }
}
