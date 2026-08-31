<?php

use App\Enums\Status;
use App\Models\Page;
use App\Models\PageMeta;
use App\PageMeta\Catalog;
use App\PageMeta\Definitions\AudiencePageDefinition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacy = Page::query()->where('slug', 'audience')->first();

        if ($legacy !== null) {
            foreach (AudiencePageDefinition::SLUGS as $slug) {
                $definition = Catalog::for($slug);
                $page = Page::query()->firstOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => $definition->title(),
                        'meta_description' => $definition->metaDescription(),
                        'status' => $legacy->status ?? Status::Active,
                    ],
                );

                $prefix = $slug.'.';

                foreach ($legacy->meta as $row) {
                    if (! str_starts_with($row->key, $prefix)) {
                        continue;
                    }

                    $remainder = substr($row->key, strlen($prefix));

                    if ($remainder === 'nav_label') {
                        continue;
                    }

                    $key = str_starts_with($remainder, 'card.')
                        ? $remainder
                        : 'hero.'.$remainder;

                    PageMeta::query()->firstOrCreate(
                        [
                            'page_id' => $page->id,
                            'key' => $key,
                            'sort_order' => $row->sort_order,
                        ],
                        ['value' => $row->value],
                    );
                }
            }

            PageMeta::query()->where('page_id', $legacy->id)->forceDelete();
            $legacy->forceDelete();
        }

        if (Schema::hasTable('content_permissions')) {
            $permissions = DB::table('content_permissions')
                ->where('resource', 'audience')
                ->whereNull('deleted_at')
                ->get();

            foreach ($permissions as $permission) {
                foreach (AudiencePageDefinition::SLUGS as $slug) {
                    $exists = DB::table('content_permissions')
                        ->where('user_id', $permission->user_id)
                        ->where('resource', $slug)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table('content_permissions')->insert([
                        'user_id' => $permission->user_id,
                        'resource' => $slug,
                        'created_by' => $permission->created_by,
                        'updated_by' => $permission->updated_by,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('content_permissions')->where('id', $permission->id)->delete();
            }
        }

        $hrefs = [
            '/audience?slug=architect-designer' => '/architect-designer',
            '/audience?slug=electrician-builder' => '/electrician-builder',
            '/audience?slug=wholesaler' => '/wholesaler',
            '/audience?slug=home-owner' => '/home-owner',
        ];

        foreach ($hrefs as $from => $to) {
            PageMeta::query()->where('value', $from)->update(['value' => $to]);
        }
    }

    public function down(): void
    {
        // Audience pages cannot be merged back automatically.
    }
};
