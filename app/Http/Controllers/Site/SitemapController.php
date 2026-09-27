<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\SitemapBuilder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        private SitemapBuilder $builder,
    ) {}

    public function index(): Response
    {
        $xml = $this->builder->getCachedXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=3600',
        ]);
    }
}
