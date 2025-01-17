<?php

namespace App\Providers;

use App\Contracts\PostmarkFactoryInterface;
use App\Factories\PostmarkFactory;
use Illuminate\Support\ServiceProvider;

class PostmarkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PostmarkFactoryInterface::class, function ($app) {
            return new PostmarkFactory($app->make('cache.store'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
