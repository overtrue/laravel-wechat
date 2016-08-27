<?php

namespace Overtrue\LaravelWechat\Middleware;

use Closure;
use Event;
use Overtrue\LaravelWechat\Events\WeChatUserAuthorized;
use EasyWeChat\Foundation\Application;


/**
 * Class OAuthAuthenticate.
 */
class OAuthAuthenticate
{
    /**
     * Use Service Container would be much artisan.
     *
     */
    public $wechat;

    public function __construct(Application $wechat)
    {
        $this->wechat = $wechat;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $isNewSession = false;

        if (!session('wechat.oauth_user')) {
            if ($request->has('state') && $request->has('code')) {
                session(['wechat.oauth_user' => $this->wechat->oauth->user()]);
                $isNewSession = true;

                return redirect()->to($this->getTargetUrl($request));
            }

            $scopes = config('wechat.oauth.scopes', ['snsapi_base']);

            if (is_string($scopes)) {
                $scopes = array_map('trim', explode(',', $scopes));
            }

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
    public function getTargetUrl($request)
    {
        $queries = array_except($request->query(), ['code', 'state']);

        return $request->url().(empty($queries) ? '' : '?'.http_build_query($queries));
    }
}
