<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdatePageContentRequest;
use App\Models\Page;
use App\Services\Contracts\IPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View as PageView;

class PageContentController extends Controller
{
    public function __construct(private IPageService $pages) {}

    public function index(): PageView
    {
        $slugs = auth()->user()->managedPageSlugs();
        abort_unless($slugs !== [], 403);

        return view('dashboard.pages.index', [
            'pages' => $this->pages->dashboardList($slugs),
        ]);
    }

    public function preview(Page $page): PageView
    {
        $preview = $this->pages->preview($page);

        return view($preview['view'], $preview['data']);
    }

    public function edit(Page $page): PageView
    {
        return view('dashboard.pages.edit', $this->pages->editorData($page));
    }

    public function update(UpdatePageContentRequest $request, Page $page): RedirectResponse
    {
        $this->pages->updateContent(
            $page,
            $request->safe()->only(['title', 'meta_description', 'status']),
            $request->validated('meta') ?? [],
            $request->file('meta', []) ?? [],
        );

        $section = $request->input('editor_section');

        return redirect()
            ->route('dashboard.pages.edit', array_filter([
                'page' => $page,
                'section' => is_string($section) && $section !== '' ? $section : null,
            ]))
            ->with('status', 'Page updated.');
    }

    public function toggleStatus(Page $page): JsonResponse
    {
        $page = $this->pages->toggleStatus($page);

        return response()->json([
            'on' => $page->isActive(),
            'label' => $page->status->label(),
            'message' => $page->isActive() ? 'Page marked active.' : 'Page marked inactive.',
        ]);
    }
}
