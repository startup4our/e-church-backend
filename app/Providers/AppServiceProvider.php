<?php

namespace App\Providers;

use App\Services\ChatService;
use App\Services\DateExceptionService;
use App\Services\Interfaces\IChatService;
use App\Services\Interfaces\IDateExceptionService;
use App\Services\Interfaces\IMessageService;
use App\Services\Interfaces\IPermissionService;
use App\Services\Interfaces\IUnavailabilityService;
use App\Services\MessageService;
use App\Services\PermissionService;
use App\Services\UnavailabilityService;
use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\IAreaService;
use App\Services\Interfaces\IChurchService;
use App\Services\Interfaces\ISongService;
use App\Services\Interfaces\IRoleService;
use App\Services\AreaService;
use App\Services\ChurchService;
use App\Services\Interfaces\ILinkService;
use App\Services\Interfaces\IRecordingService;
use App\Services\Interfaces\IScheduleService;
use App\Services\Interfaces\IUserScheduleService;
use App\Services\LinkService;
use App\Services\RecordingService;
use App\Services\SongService;
use App\Services\RoleService;
use App\Services\ScheduleService;
use App\Services\UserScheduleService;

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
        $this->app->bind(IUnavailabilityService::class, UnavailabilityService::class);
        $this->app->bind(IMessageService::class, MessageService::class);
        $this->app->bind(IDateExceptionService::class, DateExceptionService::class);
        $this->app->bind(IPermissionService::class, PermissionService::class);
        $this->app->bind(ILinkService::class, LinkService::class);
        $this->app->bind(IRecordingService::class, RecordingService::class);
        $this->app->bind(IScheduleService::class, ScheduleService::class);
        $this->app->bind(IUserScheduleService::class, UserScheduleService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
