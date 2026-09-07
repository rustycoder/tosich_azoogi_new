<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IPageService;
use App\Services\Contracts\IPageVisitService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        private IPageService $pages,
        private IPageVisitService $visits,
    ) {}

    public function __invoke(Request $request, string $slug = 'home'): View
    {
        $page = $this->pages->publicPage($slug);

        $this->visits->recordPage($slug, $request);

        return view($page['view'], $page['data']);
    }
}
