<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreDatasheetTemplateRequest;
use App\Http\Requests\Dashboard\UpdateDatasheetTemplateRequest;
use App\Models\DatasheetTemplate;
use App\Services\Contracts\IProductDatasheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DatasheetTemplateController extends Controller
{
    public function __construct(private IProductDatasheetService $datasheets) {}

    public function index(Request $request): View
    {
        $search = dash_search_query($request->query('q'));

        $query = DatasheetTemplate::query()->with(['creator', 'updater']);

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.datasheet-templates.index', [
            'templates' => $templates,
            'search' => $search,
            'totalCount' => DatasheetTemplate::count(),
            'activeTemplate' => DatasheetTemplate::query()->where('is_active', true)->first(),
        ]);
    }

    public function create(): View
    {
        $template = new DatasheetTemplate([
            'name' => '',
            'slug' => '',
            'description' => '',
            'is_active' => false,
            'body_html' => DatasheetTemplate::defaultHtml(),
            'custom_css' => DatasheetTemplate::defaultCss(),
            'available_tokens' => DatasheetTemplate::defaultTokens(),
        ]);

        $sampleData = $this->datasheets->sampleData();
        $previewHtml = $template->render($sampleData);

        return view('dashboard.datasheet-templates.create', [
            'template' => $template,
            'sampleData' => $sampleData,
            'previewHtml' => $previewHtml,
        ]);
    }

    public function store(StoreDatasheetTemplateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (DatasheetTemplate::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $isActive = $request->boolean('is_active');
        $validated['is_active'] = false;

        $template = DatasheetTemplate::create($validated);

        if ($isActive) {
            $template->makeActive();
        }

        return redirect()
            ->route('dashboard.datasheet-templates.edit', $template)
            ->with('status', 'Datasheet template created successfully.');
    }

    public function edit(DatasheetTemplate $datasheetTemplate): View
    {
        $sampleData = $this->datasheets->sampleData();
        $previewHtml = $datasheetTemplate->render($sampleData);

        return view('dashboard.datasheet-templates.edit', [
            'template' => $datasheetTemplate,
            'sampleData' => $sampleData,
            'previewHtml' => $previewHtml,
        ]);
    }

    public function update(UpdateDatasheetTemplateRequest $request, DatasheetTemplate $datasheetTemplate): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $isActive = $request->boolean('is_active');
        unset($validated['is_active']);

        $datasheetTemplate->update($validated);

        if ($isActive) {
            $datasheetTemplate->makeActive();
        } else {
            $datasheetTemplate->update(['is_active' => false]);
        }

        return redirect()
            ->route('dashboard.datasheet-templates.edit', $datasheetTemplate)
            ->with('status', 'Datasheet template updated successfully.');
    }

    public function activate(DatasheetTemplate $datasheetTemplate, Request $request): JsonResponse|RedirectResponse
    {
        $datasheetTemplate->makeActive();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Template '{$datasheetTemplate->name}' is now active for datasheet generation.",
            ]);
        }

        $referer = $request->header('Referer');
        $target = $referer ?: route('dashboard.datasheet-templates.index');

        return redirect()
            ->to($target)
            ->with('status', "Template '{$datasheetTemplate->name}' is now active for datasheet generation.");
    }

    public function duplicate(DatasheetTemplate $datasheetTemplate): RedirectResponse
    {
        $copy = $datasheetTemplate->replicate();
        $copy->name = "{$datasheetTemplate->name} (Copy)";

        $baseSlug = Str::slug($copy->name);
        $slug = $baseSlug;
        $counter = 1;
        while (DatasheetTemplate::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        $copy->slug = $slug;
        $copy->is_active = false;
        $copy->save();

        return redirect()
            ->route('dashboard.datasheet-templates.edit', $copy)
            ->with('status', "Template duplicated as '{$copy->name}'.");
    }

    public function reset(DatasheetTemplate $datasheetTemplate): RedirectResponse
    {
        $datasheetTemplate->update([
            'body_html' => DatasheetTemplate::defaultHtml(),
            'custom_css' => DatasheetTemplate::defaultCss(),
            'available_tokens' => DatasheetTemplate::defaultTokens(),
        ]);

        return redirect()
            ->route('dashboard.datasheet-templates.edit', $datasheetTemplate)
            ->with('status', 'Datasheet template reset to default layout and styles.');
    }

    public function preview(Request $request, ?DatasheetTemplate $datasheetTemplate = null): Response|JsonResponse
    {
        $sampleData = $this->datasheets->sampleData();

        $html = (string) $request->input('body_html', $datasheetTemplate?->body_html ?? DatasheetTemplate::defaultHtml());
        $css = (string) $request->input('custom_css', $datasheetTemplate?->custom_css ?? DatasheetTemplate::defaultCss());
        $autoPrint = $request->boolean('print');

        $temporaryTemplate = new DatasheetTemplate([
            'body_html' => $html,
            'custom_css' => $css,
        ]);

        $rendered = $temporaryTemplate->render($sampleData, $autoPrint);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'rendered' => $rendered,
            ]);
        }

        return response($rendered, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function destroy(DatasheetTemplate $datasheetTemplate): RedirectResponse
    {
        if ($datasheetTemplate->is_active) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Cannot delete the active datasheet template. Please activate another template first.']);
        }

        $name = $datasheetTemplate->name;
        $datasheetTemplate->delete();

        return redirect()
            ->route('dashboard.datasheet-templates.index')
            ->with('status', "Datasheet template '{$name}' deleted successfully.");
    }
}
