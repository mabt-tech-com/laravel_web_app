<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::defaultStringLength(191);

        Model::shouldBeStrict();

        ResetPassword::createUrlUsing(function (User $user, $token) {
            return config('custom_config.front_link') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });
    }
}
