<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\PageContentController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\ProjectController;
use App\Http\Controllers\Dashboard\SectionController;
use App\Http\Controllers\Dashboard\StaffController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ProjectController as SiteProjectController;
use App\PageMeta\Definitions\AudiencePageDefinition;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', DashboardController::class)->name('home');
    Route::get('settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('settings', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('admin')->group(function () {
        Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
        Route::patch('staff/{staff}/status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
    });

    Route::middleware('can.manage:projects')->group(function () {
        Route::get('content/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('content/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('content/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::patch('content/projects/reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');
        Route::get('content/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('content/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::patch('content/projects/{project}/status', [ProjectController::class, 'toggleStatus'])->name('projects.toggle-status');
        Route::patch('content/projects/{project}/featured', [ProjectController::class, 'toggleFeatured'])->name('projects.toggle-featured');
        Route::delete('content/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });

    Route::get('content/pages', [PageContentController::class, 'index'])
        ->name('pages.index');
    Route::get('content/pages/{page:slug}/preview', [PageContentController::class, 'preview'])
        ->middleware('can.manage')
        ->name('pages.preview');
    Route::get('content/pages/{page:slug}', [PageContentController::class, 'edit'])
        ->middleware('can.manage')
        ->name('pages.edit');
    Route::put('content/pages/{page:slug}', [PageContentController::class, 'update'])
        ->middleware('can.manage')
        ->name('pages.update');
    Route::patch('content/pages/{page:slug}/status', [PageContentController::class, 'toggleStatus'])
        ->middleware('can.manage')
        ->name('pages.toggle-status');

    Route::get('content/sections', [SectionController::class, 'index'])
        ->name('sections.index');
    Route::get('content/sections/{page:slug}', [SectionController::class, 'edit'])
        ->middleware('can.manage')
        ->name('sections.edit');
    Route::put('content/sections/{page:slug}', [SectionController::class, 'update'])
        ->middleware('can.manage')
        ->name('sections.update');
});

Route::get('/', [PageController::class, '__invoke'])->name('home');
Route::get('/about', [PageController::class, '__invoke'])->defaults('slug', 'about')->name('about');
Route::get('/solutions', [PageController::class, '__invoke'])->defaults('slug', 'solutions')->name('solutions');
Route::get('/casambi', [PageController::class, '__invoke'])->defaults('slug', 'casambi')->name('casambi');
Route::get('/silvair', [PageController::class, '__invoke'])->defaults('slug', 'silvair')->name('silvair');
Route::get('/dali-centre', [PageController::class, '__invoke'])->defaults('slug', 'dali-centre')->name('dali-centre');
Route::get('/madrix', [PageController::class, '__invoke'])->defaults('slug', 'madrix')->name('madrix');
Route::get('/ai-lighting', [PageController::class, '__invoke'])->defaults('slug', 'ai-lighting')->name('ai-lighting');
Route::get('/data-centre', [PageController::class, '__invoke'])->defaults('slug', 'data-centre')->name('data-centre');
Route::get('/contact', [PageController::class, '__invoke'])->defaults('slug', 'contact')->name('contact');
Route::get('/privacy', [PageController::class, '__invoke'])->defaults('slug', 'privacy')->name('privacy');
Route::get('/terms', [PageController::class, '__invoke'])->defaults('slug', 'terms')->name('terms');
Route::get('/warranty-returns', [PageController::class, '__invoke'])->defaults('slug', 'warranty-returns')->name('warranty-returns');
Route::get('/modern-slavery', [PageController::class, '__invoke'])->defaults('slug', 'modern-slavery')->name('modern-slavery');
Route::get('/home-owner', [PageController::class, '__invoke'])->defaults('slug', 'home-owner')->name('home-owner');
Route::get('/architect-designer', [PageController::class, '__invoke'])->defaults('slug', 'architect-designer')->name('architect-designer');
Route::get('/electrician-builder', [PageController::class, '__invoke'])->defaults('slug', 'electrician-builder')->name('electrician-builder');
Route::get('/wholesaler', [PageController::class, '__invoke'])->defaults('slug', 'wholesaler')->name('wholesaler');
Route::get('/audience', function () {
    $slug = request()->query('slug', 'home-owner');

    if (! in_array($slug, AudiencePageDefinition::SLUGS, true)) {
        $slug = 'home-owner';
    }

    return redirect('/'.$slug, 301);
})->name('audience');
Route::get('/projects', [SiteProjectController::class, 'index'])->name('projects');
Route::get('/project-detail', [SiteProjectController::class, 'show'])->name('project-detail');

Route::view('/products', 'pages.products')->name('products');
Route::view('/product-detail', 'pages.product-detail')->name('product-detail');
Route::view('/led-strip-calculator', 'pages.led-strip-calculator')->name('led-strip-calculator');
Route::view('/trade-login', 'pages.trade-login')->name('trade-login');
Route::view('/jr-neon', 'pages.jr-neon')->name('jr-neon');
Route::view('/test-configuration', 'pages.test-configuration')->name('test-configuration');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.submit');

Route::redirect('/policies', '/privacy', 301);
Route::redirect('policies.html', '/privacy', 301);

$htmlAliases = [
    'index.html' => '/',
    'products.html' => '/products',
    'product-detail.html' => '/product-detail',
    'projects.html' => '/projects',
    'project-detail.html' => '/project-detail',
    'about.html' => '/about',
    'solutions.html' => '/solutions',
    'casambi.html' => '/casambi',
    'silvair.html' => '/silvair',
    'dali-centre.html' => '/dali-centre',
    'madrix.html' => '/madrix',
    'contact.html' => '/contact',
    'ai-lighting.html' => '/ai-lighting',
    'led-strip-calculator.html' => '/led-strip-calculator',
    'trade_login.html' => '/trade-login',
    'audience.html' => '/audience',
    'data-centre.html' => '/data-centre',
    'jr-neon.html' => '/jr-neon',
    'test-configuration.html' => '/test-configuration',
];

foreach ($htmlAliases as $from => $to) {
    Route::redirect($from, $to, 301);
}
