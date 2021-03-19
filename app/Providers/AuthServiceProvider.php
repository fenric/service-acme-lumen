<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            return $this->app->get(UserRepository::class)
                ->findByAccessToken($request->bearerToken());
        });
    }
}
