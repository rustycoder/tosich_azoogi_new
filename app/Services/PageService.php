<?php

namespace App\Services;

use App\Models\Page;
use App\PageMeta\Catalog;
use App\PageMeta\Definitions\AudiencePageDefinition;
use App\PageMeta\EditorSections;
use App\PageMeta\SectionItems;
use App\Repositories\Contracts\IPageRepository;
use App\Repositories\Contracts\IProjectRepository;
use App\Services\Contracts\IPageService;
use App\Support\ContentStorage;
use App\Support\PageMetaBag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageService implements IPageService
{
    public function __construct(
        private IPageRepository $pages,
        private IProjectRepository $projects,
        private ContentStorage $storage,
    ) {}

    public function publicPage(string $slug): array
    {
        if (! Catalog::has($slug) || Catalog::isSection($slug)) {
            throw new NotFoundHttpException;
        }

        return $this->present($this->pages->findActiveBySlug($slug));
    }

    public function preview(Page $page): array
    {
        if (! Catalog::has($page->slug)) {
            throw new NotFoundHttpException;
        }

        return $this->present($page);
    }

    public function dashboardList(array $slugs): Collection
    {
        $pages = $this->pages->findBySlugs($slugs)->keyBy('slug');

        return collect(Catalog::slugs())
            ->filter(fn (string $slug): bool => in_array($slug, $slugs, true))
            ->map(fn (string $slug): ?Page => $pages->get($slug))
            ->filter()
            ->sortBy(fn (Page $page): string => mb_strtolower(Catalog::for($page->slug)->navLabel()), SORT_NATURAL)
            ->values();
    }

    public function editorData(Page $page): array
    {
        $page->loadMissing('meta');
        $definition = Catalog::for($page->slug);

        return [
            'page' => $page,
            'definition' => $definition,
            'metaByKey' => $page->meta->groupBy('key'),
            'sections' => EditorSections::for($definition),
            'previewUrl' => route('dashboard.pages.preview', $page),
        ];
    }

    public function updateContent(Page $page, array $attributes, array $metaValues, array $uploaded, array $items = []): void
    {
        $page->fill($attributes);
        $this->pages->save($page);

        foreach ($metaValues as $id => $payload) {
            $meta = $this->pages->findMeta($page, (int) $id);

            if ($meta === null) {
                continue;
            }

            $file = $uploaded[$id]['file'] ?? null;

            if ($file instanceof UploadedFile) {
                $meta->value = $this->storage->storePageUpload(
                    $page->slug,
                    $meta->key,
                    $meta->sort_order,
                    $file,
                    $meta->value,
                );
            } elseif (array_key_exists('value', $payload)) {
                $meta->value = $payload['value'];
            }

            $this->pages->saveMeta($meta);
        }

        $this->syncItems($page, $items);
    }

    /**
     * @param  array<string, mixed>  $items
     */
    private function syncItems(Page $page, array $items): void
    {
        foreach (SectionItems::aliases($page->slug) as $alias => $prefix) {
            if (! array_key_exists($alias, $items) || ! is_array($items[$alias])) {
                continue;
            }

            $this->pages->deleteMetaByPrefix($page, $prefix);

            $order = 0;

            foreach ($items[$alias] as $key => $row) {
                if ($key === '_sync' || ! is_array($row)) {
                    continue;
                }

                if ($prefix === 'header.word') {
                    $text = trim((string) ($row['text'] ?? ''));

                    if ($text === '') {
                        continue;
                    }

                    $this->pages->createMeta($page, $prefix.'.text', $order, $text);
                    $order++;

                    continue;
                }

                $label = trim((string) ($row['label'] ?? ''));
                $href = trim((string) ($row['href'] ?? ''));

                if ($label === '' && $href === '') {
                    continue;
                }

                $this->pages->createMeta($page, $prefix.'.label', $order, $label);
                $this->pages->createMeta($page, $prefix.'.href', $order, $href);
                $this->pages->createMeta(
                    $page,
                    $prefix.'.target',
                    $order,
                    ($row['target'] ?? '_self') === '_blank' ? '_blank' : '_self',
                );
                $order++;
            }
        }
    }

    public function toggleStatus(Page $page): Page
    {
        $page->status = $page->status->toggle();
        $this->pages->save($page);

        return $page;
    }

    /**
     * @return array{view: string, data: array<string, mixed>}
     */
    private function present(Page $page): array
    {
        $page->loadMissing('meta');
        $meta = PageMetaBag::for($page);
        $data = [
            'page' => $page,
            'meta' => $meta,
            'definition' => Catalog::for($page->slug),
            'featuredProjects' => $page->slug === 'home'
                ? $this->projects->activeFeatured()
                : collect(),
        ];

        if (in_array($page->slug, AudiencePageDefinition::SLUGS, true)) {
            $data['leads'] = $meta->list('hero.lead');
            $data['cards'] = $meta->group('card');
        }

        return [
            'view' => $this->viewName($page->slug),
            'data' => $data,
        ];
    }

    private function viewName(string $slug): string
    {
        return match ($slug) {
            'home' => 'pages.home',
            'about' => 'pages.about',
            'solutions' => 'pages.solutions',
            'casambi' => 'pages.casambi',
            'silvair' => 'pages.silvair',
            'dali-centre' => 'pages.dali-centre',
            'madrix' => 'pages.madrix',
            'ai-lighting' => 'pages.ai-lighting',
            'data-centre' => 'pages.data-centre',
            'contact' => 'pages.contact',
            'home-owner', 'architect-designer', 'electrician-builder', 'wholesaler' => 'pages.audience',
            'privacy', 'terms', 'warranty-returns', 'modern-slavery' => 'pages.legal',
            default => throw new NotFoundHttpException,
        };
    }
}
