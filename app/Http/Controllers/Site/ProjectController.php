<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IPageService;
use App\Services\Contracts\IProjectService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private IPageService $pages,
        private IProjectService $projects,
    ) {}

    public function index(): View
    {
        $presented = $this->pages->publicPage('projects');

        return view($presented['view'], $presented['data']);
    }

    public function show(Request $request): View
    {
        $presented = $this->pages->publicPage('projects');

        return view('pages.project-detail', [
            ...$presented['data'],
            'project' => $this->projects->publicDetail((string) $request->query('slug')),
        ]);
    }
}
