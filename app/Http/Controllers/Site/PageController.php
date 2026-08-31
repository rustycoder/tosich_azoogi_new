<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IPageService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private IPageService $pages) {}

    public function __invoke(string $slug = 'home'): View
    {
        $page = $this->pages->publicPage($slug);

        return view($page['view'], $page['data']);
    }
}
