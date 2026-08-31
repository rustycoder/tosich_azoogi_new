<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

$pages = [
    '/' => 'pages.home',
    '/products' => 'pages.products',
    '/product-detail' => 'pages.product-detail',
    '/projects' => 'pages.projects',
    '/project-detail' => 'pages.project-detail',
    '/about' => 'pages.about',
    '/solutions' => 'pages.solutions',
    '/contact' => 'pages.contact',
    '/ai-lighting' => 'pages.ai-lighting',
    '/led-strip-calculator' => 'pages.led-strip-calculator',
    '/policies' => 'pages.policies',
    '/trade-login' => 'pages.trade-login',
    '/audience' => 'pages.audience',
    '/data-centre' => 'pages.data-centre',
    '/jr-neon' => 'pages.jr-neon',
    '/test-configuration' => 'pages.test-configuration',
];

foreach ($pages as $uri => $view) {
    Route::view($uri, $view)->name(ltrim(str_replace('/', '.', $uri), '.') ?: 'home');
}

Route::post('/contact', [ContactController::class, 'store'])->name('contact.submit');

$htmlAliases = [
    'index.html' => '/',
    'products.html' => '/products',
    'product-detail.html' => '/product-detail',
    'projects.html' => '/projects',
    'project-detail.html' => '/project-detail',
    'about.html' => '/about',
    'solutions.html' => '/solutions',
    'contact.html' => '/contact',
    'ai-lighting.html' => '/ai-lighting',
    'led-strip-calculator.html' => '/led-strip-calculator',
    'policies.html' => '/policies',
    'trade_login.html' => '/trade-login',
    'audience.html' => '/audience',
    'data-centre.html' => '/data-centre',
    'jr-neon.html' => '/jr-neon',
    'test-configuration.html' => '/test-configuration',
];

foreach ($htmlAliases as $from => $to) {
    Route::redirect($from, $to, 301);
}
