<?php

namespace App\PageMeta;

interface PageDefinition
{
    public function slug(): string;

    public function title(): string;

    public function metaDescription(): string;

    public function navLabel(): string;

    /**
     * @return list<Field>
     */
    public function fields(): array;

    /**
     * @return list<array{key: string, sort_order: int, value: string}>
     */
    public function seed(): array;
}
