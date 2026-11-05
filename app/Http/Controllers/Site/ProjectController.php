<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IPageService;
use App\Services\Contracts\IProjectService;
use Illuminate\Http\RedirectResponse;
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

    public function show(string $slug): View
    {
        $presented = $this->pages->publicPage('projects');

        return view('pages.project-detail', [
            ...$presented['data'],
            'project' => $this->projects->publicDetail($slug),
        ]);
    }

    /**
     * Permanently redirect legacy /project-detail?slug= links to /projects/{slug}.
     */
    public function redirectLegacyDetail(Request $request): RedirectResponse
    {
        $slug = trim((string) $request->query('slug'));

        abort_if($slug === '', 404);

        return redirect()->route('projects.show', ['slug' => $slug], 301);
    }
}
