<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdatePageContentRequest;
use App\Models\Page;
use App\PageMeta\Catalog;
use App\Services\Contracts\IPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View as PageView;

class SectionController extends Controller
{
    public function __construct(private IPageService $pages) {}

    public function index(Request $request): PageView
    {
        $slugs = auth()->user()->managedSectionSlugs();
        abort_unless($slugs !== [], 403);

        $search = dash_search_query($request->query('q'));
        $order = array_flip(Catalog::sectionSlugs());

        return view('dashboard.sections.index', [
            'pages' => $this->pages->dashboardList($slugs, $search)
                ->sortBy(fn (Page $page): int => $order[$page->slug] ?? 99)
                ->values(),
            'search' => $search,
        ]);
    }

    public function edit(Page $page): PageView
    {
        abort_unless(Catalog::isSection($page->slug), 404);

        return view('dashboard.sections.edit', $this->pages->editorData($page));
    }

    public function update(UpdatePageContentRequest $request, Page $page): RedirectResponse
    {
        abort_unless(Catalog::isSection($page->slug), 404);

        $this->pages->updateContent(
            $page,
            $request->safe()->only(['title', 'meta_description', 'status']),
            $request->validated('meta') ?? [],
            $request->file('meta', []) ?? [],
            $request->validated('items') ?? [],
        );

        return redirect()
            ->route('dashboard.sections.edit', $page)
            ->with('status', 'Section updated.');
    }
}
