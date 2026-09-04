<?php

namespace App\Providers;

use App\Models\Page;
use App\PageMeta\Catalog;
use App\Repositories\ContentPermissionRepository;
use App\Repositories\Contracts\IContentPermissionRepository;
use App\Repositories\Contracts\IPageRepository;
use App\Repositories\Contracts\IProductRepository;
use App\Repositories\Contracts\IProjectRepository;
use App\Repositories\Contracts\IUserRepository;
use App\Repositories\PageRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use App\Services\Contracts\IPageService;
use App\Services\Contracts\IProductSyncService;
use App\Services\Contracts\IProfileService;
use App\Services\Contracts\IProjectService;
use App\Services\Contracts\IStaffService;
use App\Services\PageService;
use App\Services\ProductSyncService;
use App\Services\ProfileService;
use App\Services\ProjectService;
use App\Services\StaffService;
use App\Support\PageMetaBag;
use App\ThirdParty\Airtable\AirtableClient;
use App\ThirdParty\Airtable\Contracts\IAirtableClient;
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
        $this->app->bind(IProductRepository::class, ProductRepository::class);
        $this->app->bind(IAirtableClient::class, AirtableClient::class);

        $this->app->bind(IPageService::class, PageService::class);
        $this->app->bind(IProjectService::class, ProjectService::class);
        $this->app->bind(IStaffService::class, StaffService::class);
        $this->app->bind(IProfileService::class, ProfileService::class);
        $this->app->bind(IProductSyncService::class, ProductSyncService::class);
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
                'canManageProducts' => $user?->canManageProducts() ?? false,
                'canManageSections' => $user?->canManageSections() ?? false,
                'isAdmin' => $user?->isAdmin() ?? false,
            ]);
        });

        View::composer('layouts.site', function ($view): void {
            $pages = Page::query()
                ->whereIn('slug', Catalog::sectionSlugs())
                ->with('meta')
                ->get()
                ->keyBy('slug');

            $view->with([
                'headerMeta' => isset($pages['header']) ? PageMetaBag::for($pages['header']) : PageMetaBag::empty(),
                'footerMeta' => isset($pages['footer']) ? PageMetaBag::for($pages['footer']) : PageMetaBag::empty(),
                'productCatalog' => $this->app->make(IProductRepository::class)->compiled(),
            ]);
        });
    }
}
