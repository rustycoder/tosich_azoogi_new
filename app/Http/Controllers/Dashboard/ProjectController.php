<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ReorderProjectsRequest;
use App\Http\Requests\Dashboard\StoreProjectRequest;
use App\Http\Requests\Dashboard\UpdateProjectRequest;
use App\Models\Project;
use App\Services\Contracts\IProjectService;
use App\Support\LlmsTxtBuilder;
use App\Support\SitemapBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private IProjectService $projects) {}

    public function index(Request $request): View
    {
        $search = dash_search_query($request->query('q'));
        $status = $request->query('status');
        $activeStatus = filled($status) && in_array($status, ['active', 'inactive'], true) ? (string) $status : null;

        $featured = $request->query('featured');
        $activeFeatured = filled($featured) && in_array($featured, ['featured', 'not_featured', '1', '0'], true) ? (string) $featured : null;

        $rawPerPage = (int) $request->query('per_page', 15);
        $perPage = in_array($rawPerPage, [15, 25, 50, 100], true) ? $rawPerPage : 15;

        $projects = $this->projects->dashboardList($search, $activeStatus, $activeFeatured, $perPage);

        return view('dashboard.projects.index', [
            'projects' => $projects,
            'search' => $search,
            'activeStatus' => $activeStatus,
            'activeFeatured' => $activeFeatured,
            'perPage' => $perPage,
            'perPageOptions' => [15, 25, 50, 100],
        ]);
    }

    public function create(): View
    {
        return view('dashboard.projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['cover_file', 'gallery_files']);

        $project = $this->projects->create(
            $data,
            $request->file('cover_file'),
            $request->file('gallery_files', []) ?? [],
        );

        SitemapBuilder::clearCache();
        LlmsTxtBuilder::clearCache();

        return redirect()->route('dashboard.projects.edit', $project)->with('status', 'Project created.');
    }

    public function edit(Project $project): View
    {
        return view('dashboard.projects.edit', ['project' => $project]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->safe()->except(['cover_file', 'gallery_files', 'gallery_sync', 'keep_gallery']);

        $this->projects->update(
            $project,
            $data,
            $request->file('cover_file'),
            $request->file('gallery_files', []) ?? [],
            $request->boolean('gallery_sync')
                ? array_map(intval(...), $request->validated('keep_gallery') ?? [])
                : null,
        );

        SitemapBuilder::clearCache();
        LlmsTxtBuilder::clearCache();

        return back()->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->projects->delete($project);
        SitemapBuilder::clearCache();
        LlmsTxtBuilder::clearCache();

        return redirect()->route('dashboard.projects.index')->with('status', 'Project deleted.');
    }

    public function toggleStatus(Project $project): JsonResponse
    {
        $project = $this->projects->toggleStatus($project);
        SitemapBuilder::clearCache();
        LlmsTxtBuilder::clearCache();

        return response()->json([
            'on' => $project->isActive(),
            'label' => $project->status->label(),
            'message' => $project->isActive() ? 'Project marked active.' : 'Project marked inactive.',
        ]);
    }

    public function toggleFeatured(Project $project): JsonResponse
    {
        $project = $this->projects->toggleFeatured($project);
        SitemapBuilder::clearCache();
        LlmsTxtBuilder::clearCache();

        return response()->json([
            'on' => $project->featured,
            'label' => $project->featured ? 'Yes' : 'No',
            'message' => $project->featured ? 'Project is now featured.' : 'Project is no longer featured.',
        ]);
    }

    public function reorder(ReorderProjectsRequest $request): JsonResponse
    {
        $this->projects->reorder($request->validated('order'));
        SitemapBuilder::clearCache();
        LlmsTxtBuilder::clearCache();

        return response()->json([
            'message' => 'Featured order updated.',
        ]);
    }
}
