<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Repositories\AuthRepository::class,
            \App\Repositories\Eloquent\AuthRepositoryEloquent::class);
        //

        $this->app->bind(\App\Repositories\MemberRepository::class,
            \App\Repositories\Eloquent\MemberRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\RegionRepository::class,
            \App\Repositories\Eloquent\RegionRepositoryEloquent::class);


        $this->app->bind(\App\Repositories\NewsRepository::class,
            \App\Repositories\Eloquent\NewsRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\ClassifyRepository::class,
            \App\Repositories\Eloquent\ClassifyRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\AttachmentRepository::class,
            \App\Repositories\Eloquent\AttachmentRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\WechatFansRepository::class,
            \App\Repositories\Eloquent\WechatFansRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\AccountRepository::class,
            \App\Repositories\Eloquent\AccountRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\NoticeRepository::class,
            \App\Repositories\Eloquent\NoticeRepositoryEloquent::class);

        $this->app->bind(\App\Repositories\HotelRepository::class,
            \App\Repositories\Eloquent\HotelRepositoryEloquent::class);
    }
}
