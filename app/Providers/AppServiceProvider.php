<?php

namespace App\Providers;

use App\Services\AreaService;
use App\Services\Interfaces\IAreaService;
use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\IChurchService;
use App\Services\Interfaces\ISongService;
use App\Services\ChurchService;
use App\Services\SongService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IAreaService::class, AreaService::class);
        $this->app->bind(IChurchService::class, ChurchService::class);
        $this->app->bind(ISongService::class, SongService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
