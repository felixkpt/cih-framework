<?php

namespace Cih\Framework;

use Cih\Framework\Middleware\AuthMenus;
use Illuminate\Support\ServiceProvider;

class CihServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var Router $router */
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('member', AuthMenus::class);
    }
}

include 'Repositories/helperrepo.php';
