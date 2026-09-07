<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (DB::table('content_permissions')->where('resource', 'products')->get() as $row) {
            $exists = DB::table('content_permissions')
                ->where('user_id', $row->user_id)
                ->where('resource', 'datasheet')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('content_permissions')->insert([
                'user_id' => $row->user_id,
                'resource' => 'datasheet',
                'created_by' => $row->created_by,
                'updated_by' => $row->updated_by,
                'deleted_by' => $row->deleted_by,
                'created_at' => $row->created_at,
                'updated_at' => $now,
                'deleted_at' => $row->deleted_at,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('content_permissions')->where('resource', 'datasheet')->delete();
    }
};
