<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $replacements = ['quote-enquiries', 'product-enquiries', 'contact-enquiry'];
        $now = now();

        foreach (DB::table('content_permissions')->where('resource', 'enquiries')->get() as $row) {
            foreach ($replacements as $index => $resource) {
                if ($index === 0) {
                    DB::table('content_permissions')->where('id', $row->id)->update([
                        'resource' => $resource,
                        'updated_at' => $now,
                    ]);

                    continue;
                }

                $exists = DB::table('content_permissions')
                    ->where('user_id', $row->user_id)
                    ->where('resource', $resource)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('content_permissions')->insert([
                    'user_id' => $row->user_id,
                    'resource' => $resource,
                    'created_by' => $row->created_by,
                    'updated_by' => $row->updated_by,
                    'deleted_by' => $row->deleted_by,
                    'created_at' => $row->created_at,
                    'updated_at' => $now,
                    'deleted_at' => $row->deleted_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        $rows = DB::table('content_permissions')
            ->whereIn('resource', ['quote-enquiries', 'product-enquiries', 'contact-enquiry'])
            ->orderBy('id')
            ->get()
            ->groupBy('user_id');

        foreach ($rows as $userRows) {
            $keep = $userRows->first();

            DB::table('content_permissions')->where('id', $keep->id)->update([
                'resource' => 'enquiries',
                'updated_at' => now(),
            ]);

            DB::table('content_permissions')
                ->where('user_id', $keep->user_id)
                ->whereIn('resource', ['quote-enquiries', 'product-enquiries', 'contact-enquiry'])
                ->delete();
        }
    }
};
