<?php

namespace App\Providers;

use App\Models\Booking;
use App\Observers\BookingObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
        Booking::observe(BookingObserver::class);
    }
}
