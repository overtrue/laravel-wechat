<?php

namespace Overtrue\LaravelWechat\Middleware;

use Closure;

/**
 * Class OAuthAuthenticate
 */
class OAuthAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $wechat = app('EasyWeChat\\Foundation\\Application', [config('wechat')]);

        if (!session('wechat.oauth_user')) {
            if ($request->has('state') && $request->has('code')) {
                session(['wechat.oauth_user' => $wechat->oauth->user()]);

                return redirect()->to($this->getTargetUrl($request));
            }

            return $wechat->oauth->redirect($request->fullUrl());
        }

        return $next($request);
    }

    /**
     * Build the target business url.
     *
     * @param Request $request
     *
     * @return string
     */
    public function getTargetUrl($request)
    {
        $queries = array_except($request->query(), ['code', 'state']);

        return $request->url().(empty($queries) ? '' : '?'.http_build_query($queries));
    }
}
