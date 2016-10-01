<?php

namespace Overtrue\LaravelWechat\Middleware;

use Closure;
use EasyWeChat\Foundation\Application;
use Event;
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
    public function __construct(Application $wechat)
    {
        $this->wechat = $wechat;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $checkRange 使用范围,all:所有环境都要授权  weixin:只在微信浏览器中授权
     * @param string                   $scopes 授权范围 公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login 不设置读取配制
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $checkRange = 'all', $scopes = null)
    {
        //授权信息范围如果有参数以参数为准,否则以config为准,默认为snsapi_base
        $scopes = $scopes ? [$scopes]: config('wechat.oauth.scopes', ['snsapi_base']);

        if (is_string($scopes)) {
            $scopes = array_map('trim', explode(',', $scopes));
        }

        //是否需要进行微信授权检查, all检查所有(只能在微信内访问) wexin(只在微信内检查,其它端忽略)
        if($checkRange == 'all' || ($checkRange == 'wexin' && $this->isWexin($request))){

            //获取授权信息条件
            //  1:session为空
            //  2:session中的授权信息为snsapi_base  本次是 snsapi_userinfo 需要重新获取
            if (!session('wechat.oauth_user') ||
                (session('wechat.oauth_user.original.scope','')=='snsapi_base' && in_array("snsapi_userinfo",$scopes))
            ) {

                # 第二步,如果拿到code 以code换取token并获取用户信息
                if ($request->has('state') && $request->has('code')) {

                    session(['wechat.oauth_user' => $this->wechat->oauth->user()]);

                    $isNewSession = true;
                    Event::fire(new WeChatUserAuthorized(session('wechat.oauth_user'), $isNewSession));

                    return redirect()->to($this->getTargetUrl($request));
                }

                # 第一步:获取code 先清空session
                session()->forget('wechat.oauth_user');
                $scopes = config('wechat.oauth.scopes', ['snsapi_base']);

                if (is_string($scopes)) {
                    $scopes = array_map('trim', explode(',', $scopes));
                }

                return $this->wechat->oauth->scopes($scopes)->redirect($request->fullUrl());
            }
        }

        //保留已
        $isNewSession = false;
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

    /**
     * 判断是当前是否为微信浏览器
     * @param $request
     * @return bool
     */
    private function isWexin($request )
    {
        if (strpos($request->header('user_agent'), 'MicroMessenger') === false){
            return false;
        }
        return true;
    }
}
