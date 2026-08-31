<?php

namespace App\Services\Contracts;

use App\Models\Page;
use App\PageMeta\Field;
use App\PageMeta\PageDefinition;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface IPageService
{
    /**
     * @return array{view: string, data: array<string, mixed>}
     */
    public function publicPage(string $slug): array;

    /**
     * @return array{view: string, data: array<string, mixed>}
     */
    public function preview(Page $page): array;

    /**
     * @param  list<string>  $slugs
     * @return Collection<int, Page>
     */
    public function dashboardList(array $slugs): Collection;

    /**
     * @return array{
     *     page: Page,
     *     definition: PageDefinition,
     *     metaByKey: Collection,
     *     sections: list<array{key: string, label: string, fields: list<Field>}>,
     *     previewUrl: string
     * }
     */
    public function editorData(Page $page): array;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array<string, mixed>>  $metaValues
     * @param  array<int, array{file?: UploadedFile|null}>  $uploaded
     */
    public function updateContent(Page $page, array $attributes, array $metaValues, array $uploaded): void;

    public function toggleStatus(Page $page): Page;
}
