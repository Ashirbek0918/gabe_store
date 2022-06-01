<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Order;
use App\Models\Basket;
use App\Observers\UserObserver;
use App\Observers\OrderObserver;
use App\Observers\BasketObserver;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Order::observe(OrderObserver::class);
        Basket::observe(BasketObserver::class);
        User::observe(UserObserver::class);
    }
}
