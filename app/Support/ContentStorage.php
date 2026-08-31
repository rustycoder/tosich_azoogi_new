<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentStorage
{
    public function storePageUpload(string $slug, string $key, int $sortOrder, UploadedFile $file, ?string $previous = null): string
    {
        $this->deleteManaged($previous);

        $directory = 'pages/'.$slug.'/'.str_replace('.', '/', $key).'/'.$sortOrder;
        $name = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();

        $file->storeAs($directory, $name, 'public');

        return '/storage/'.$directory.'/'.$name;
    }

    public function storeProjectUpload(string $slug, string $kind, UploadedFile $file, ?string $previous = null): string
    {
        $this->deleteManaged($previous);

        $directory = 'projects/'.$slug.'/'.$kind;
        $name = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();

        $file->storeAs($directory, $name, 'public');

        return '/storage/'.$directory.'/'.$name;
    }

    public function deleteManaged(?string $path): void
    {
        if ($path === null || $path === '' || ! str_starts_with($path, '/storage/')) {
            return;
        }

        $relative = ltrim(substr($path, strlen('/storage/')), '/');

        if ($relative !== '' && Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }
}
