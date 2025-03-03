<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\Frontend;

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
        app()->setLocale(request()->segment(1));
        Paginator::defaultView(config('tmdb.theme').'.pagination');

        $frontend = new Frontend;
        view()->share('genre_list', $frontend->genreLists());
    }
}
