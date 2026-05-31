<?php

namespace App\Providers;

use App\Models\Property;
use App\Observers\PropertyObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        mb_internal_encoding('UTF-8');
        Schema::defaultStringLength(191);
        Model::unguard();

        Property::observe(PropertyObserver::class);
    }
}
