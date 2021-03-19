<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\UserRepository;
use App\Services\UserManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserRepository::class, function ($app) {
            return new UserRepository();
        });

        $this->app->singleton(UserManager::class, function ($app) {
            return new UserManager();
        });
    }
}
