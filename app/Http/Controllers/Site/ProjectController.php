<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IProjectService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private IProjectService $projects) {}

    public function index(): View
    {
        return view('pages.projects', $this->projects->publicListing());
    }

    public function show(Request $request): View
    {
        return view('pages.project-detail', [
            'project' => $this->projects->publicDetail((string) $request->query('slug')),
        ]);
    }
}
