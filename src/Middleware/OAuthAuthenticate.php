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
        $wechat = app('EasyWeChat\\Foundation\\Application');

        if (!session('wechat.oauth_user')) {
            if ($request->has('state') && $request->has('code')) {
                session(['wechat.oauth_user' => $wechat->oauth->user()]);

                return redirect()->to(url($request->url().'?'.array_except($request->query(), ['code', 'state'])));
            }

            return $wechat->oauth->redirect($request->fullUrl());
        }

        return $next($request);
    }
}
