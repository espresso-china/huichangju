<?php

namespace Xndl\YsePay\YsePay;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Foundation\Application as LaravelApplication;

class YsePayServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig($this->app);
        $this->setupMigrations($this->app);
    }

    /**
     * 初始化配置
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    protected function setupConfig(Application $app)
    {
        $source = realpath(__DIR__ . '/../../config/ysepay.php');

        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => config_path('ysepay.php')]);
        } elseif ($app instanceof LumenApplication) {
            $app->configure('ysepay');
        }

        $this->mergeConfigFrom($source, 'ysepay');
    }

    /**
     * 初始化数据库
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    protected function setupMigrations(Application $app)
    {
        $source = realpath(__DIR__ . '/../../database/migrations/');

        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => database_path('migrations')], 'migrations');
        }
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/ysepay.php', 'ysepay'
        );

        $this->app->singleton('ysepay', function ($app) {
            $app = new YsePay(config('ysepay'));

            return $app;
        });
    }


    /**
     * 提供的服务
     *
     * @return array
     */
    public function provides()
    {
        return [YsePay::class, 'ysepay'];
    }
}
