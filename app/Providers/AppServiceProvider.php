<?php

namespace App\Providers;

use App\Repositories\ContentPermissionRepository;
use App\Repositories\Contracts\IContentPermissionRepository;
use App\Repositories\Contracts\IPageRepository;
use App\Repositories\Contracts\IProjectRepository;
use App\Repositories\Contracts\IUserRepository;
use App\Repositories\PageRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use App\Services\Contracts\IPageService;
use App\Services\Contracts\IProfileService;
use App\Services\Contracts\IProjectService;
use App\Services\Contracts\IStaffService;
use App\Services\PageService;
use App\Services\ProfileService;
use App\Services\ProjectService;
use App\Services\StaffService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IPageRepository::class, PageRepository::class);
        $this->app->bind(IProjectRepository::class, ProjectRepository::class);
        $this->app->bind(IUserRepository::class, UserRepository::class);
        $this->app->bind(IContentPermissionRepository::class, ContentPermissionRepository::class);

        $this->app->bind(IPageService::class, PageService::class);
        $this->app->bind(IProjectService::class, ProjectService::class);
        $this->app->bind(IStaffService::class, StaffService::class);
        $this->app->bind(IProfileService::class, ProfileService::class);
    }

    public function boot(): void
    {
        Route::bind('staff', function (string $value) {
            return $this->app->make(IUserRepository::class)->findStaffOrFail($value);
        });

        View::composer(['layouts.dashboard', 'dashboard.*'], function ($view): void {
            $user = auth()->user();

            $view->with([
                'canManagePages' => $user?->canManagePages() ?? false,
                'canManageProjects' => $user?->canManageProjects() ?? false,
                'isAdmin' => $user?->isAdmin() ?? false,
            ]);
        });
    }
}
