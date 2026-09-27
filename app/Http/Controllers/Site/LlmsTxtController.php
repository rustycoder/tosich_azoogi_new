<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\LlmsTxtBuilder;
use Illuminate\Http\Response;

class LlmsTxtController extends Controller
{
    public function __construct(
        private LlmsTxtBuilder $builder,
    ) {}

    public function index(): Response
    {
        $content = $this->builder->getCachedSummary();

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=3600',
        ]);
    }

    public function full(): Response
    {
        $content = $this->builder->getCachedFull();

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=3600',
        ]);
    }
}
