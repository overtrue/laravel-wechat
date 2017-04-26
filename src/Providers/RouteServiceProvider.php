<?php

namespace Overtrue\LaravelWechat\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Class RouteServiceProvider.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Get Route attributes.
     *
     * @return array
     */
    public function routeAttributes()
    {
        return array_merge($this->config('attributes', []), [
            'namespace' => 'Overtrue\\LaravelWechat\\Controllers',
        ]);
    }

    /**
     * Check if routes is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config('enabled', false);
    }

    /**
     * Define the routes.
     */
    public function map()
    {
        if ($this->isEnabled()) {
            $this->group($this->routeAttributes(), function () {
                $this->mapOpenPlatformRoutes();
            });
        }
    }

    /**
     * Map open platform routes.
     */
    public function mapOpenPlatformRoutes()
    {
        $this->match(['GET','POST'], $this->config('open_platform_serve_url'), 'OpenPlatformController@index')->name('open-platform');
    }

    /**
     * Get config value by key.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    private function config($key, $default = null)
    {
        /** @var  \Illuminate\Config\Repository  $config */
        $config = $this->app->make('config');

        return $config->get("wechat.route.$key", $default);
    }

    /**
     * Call the router method.
     *
     * @param string $name
     * @param array $args
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        return $this->app->make('router')->$name(...$args);
    }
}
