<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ReorderProjectsRequest;
use App\Http\Requests\Dashboard\StoreProjectRequest;
use App\Http\Requests\Dashboard\UpdateProjectRequest;
use App\Models\Project;
use App\Services\Contracts\IProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private IProjectService $projects) {}

    public function index(): View
    {
        return view('dashboard.projects.index', [
            'projects' => $this->projects->dashboardList(),
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

        return redirect()->route('dashboard.projects.edit', $project)->with('status', 'Project created.');
    }

    public function edit(Project $project): View
    {
        return view('dashboard.projects.edit', ['project' => $project]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->safe()->except(['cover_file', 'gallery_files', 'remove_gallery']);

        $this->projects->update(
            $project,
            $data,
            $request->file('cover_file'),
            $request->file('gallery_files', []) ?? [],
            $request->input('remove_gallery', []) ?? [],
        );

        return back()->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->projects->delete($project);

        return redirect()->route('dashboard.projects.index')->with('status', 'Project deleted.');
    }

    public function toggleStatus(Project $project): JsonResponse
    {
        $project = $this->projects->toggleStatus($project);

        return response()->json([
            'on' => $project->isActive(),
            'label' => $project->status->label(),
            'message' => $project->isActive() ? 'Project marked active.' : 'Project marked inactive.',
        ]);
    }

    public function toggleFeatured(Project $project): JsonResponse
    {
        $project = $this->projects->toggleFeatured($project);

        return response()->json([
            'on' => $project->featured,
            'label' => $project->featured ? 'Yes' : 'No',
            'message' => $project->featured ? 'Project is now featured.' : 'Project is no longer featured.',
        ]);
    }

    public function reorder(ReorderProjectsRequest $request): JsonResponse
    {
        $this->projects->reorder($request->validated('order'));

        return response()->json([
            'message' => 'Featured order updated.',
        ]);
    }
}
