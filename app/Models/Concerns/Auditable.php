<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::creating(function (self $model): void {
            $id = Auth::id();

            if ($id === null) {
                return;
            }

            if (! $model->created_by) {
                $model->created_by = $id;
            }

            $model->updated_by = $id;
        });

        static::updating(function (self $model): void {
            $id = Auth::id();

            if ($id !== null) {
                $model->updated_by = $id;
            }
        });

        static::deleting(function (self $model): void {
            $id = Auth::id();

            if ($id === null || $model->isForceDeleting()) {
                return;
            }

            $model->deleted_by = $id;
            $model->saveQuietly();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
