<?php

namespace App\Providers;

use App\Services\ChatService;
use App\Services\Interfaces\IChatService;
use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\IAreaService;
use App\Services\Interfaces\IChurchService;
use App\Services\Interfaces\ISongService;
use App\Services\Interfaces\IRoleService;
use App\Services\AreaService;
use App\Services\ChurchService;
use App\Services\SongService;
use App\Services\RoleService;

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
        $this->app->bind(IRoleService::class, RoleService::class);
        $this->app->bind(IChatService::class, ChatService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
