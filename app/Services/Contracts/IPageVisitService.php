<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface IPageVisitService
{
    public function recordPage(string $slug, ?Request $request = null): void;

    public function recordProduct(?string $airtableId, ?Request $request = null): void;
}
