<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class ProjectsDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'projects';
    }

    public function title(): string
    {
        return 'Projects — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Explore Azoogi LED lighting projects across hospitality, residential, medical, industrial and commercial spaces.';
    }

    public function navLabel(): string
    {
        return 'Projects';
    }

    public function fields(): array
    {
        return [
            Field::text('hero.title', 'Hero title'),
            Field::textarea('hero.body', 'Hero intro'),
            Field::text('highlights.heading', 'Highlights heading'),
            Field::text('highlights.heading_accent', 'Highlights accent'),
            Field::text('list.showing', 'Count prefix'),
            Field::text('list.singular', 'Singular count'),
            Field::text('list.plural', 'Plural count'),
            Field::text('list.fallback_tag', 'Fallback tag'),
            Field::text('detail.back', 'Back link'),
            Field::text('detail.overview', 'Overview heading'),
            Field::text('detail.location_label', 'Location label'),
            Field::text('detail.type_label', 'Type label'),
            Field::text('detail.completed_label', 'Completed label'),
        ];
    }

    public function seed(): array
    {
        return [
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'Projects Powered by Azoogi'],
            ['key' => 'hero.body', 'sort_order' => 0, 'value' => "From a new strip light in your kitchen to landmark Tier-1 developments — we deliver LED lighting solutions for projects of all sizes. Whether it’s a heritage restoration, boutique hospitality venue, residential upgrade, or a large-scale commercial build, our in-house engineering and assembly line ensure precision, speed, efficiency and quality — no matter the scale.\nFor a copy of our capability statement, contact us at majorprojects@azoogi.com."],
            ['key' => 'highlights.heading', 'sort_order' => 0, 'value' => 'Recent Highlights'],
            ['key' => 'highlights.heading_accent', 'sort_order' => 0, 'value' => 'Highlights'],
            ['key' => 'list.showing', 'sort_order' => 0, 'value' => 'Showing'],
            ['key' => 'list.singular', 'sort_order' => 0, 'value' => 'project'],
            ['key' => 'list.plural', 'sort_order' => 0, 'value' => 'projects'],
            ['key' => 'list.fallback_tag', 'sort_order' => 0, 'value' => 'Project'],
            ['key' => 'detail.back', 'sort_order' => 0, 'value' => 'All Projects'],
            ['key' => 'detail.overview', 'sort_order' => 0, 'value' => 'Project Overview'],
            ['key' => 'detail.location_label', 'sort_order' => 0, 'value' => 'Location:'],
            ['key' => 'detail.type_label', 'sort_order' => 0, 'value' => 'Type:'],
            ['key' => 'detail.completed_label', 'sort_order' => 0, 'value' => 'Completed:'],
        ];
    }
}
