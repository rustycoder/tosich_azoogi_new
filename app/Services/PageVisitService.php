<?php

namespace App\Services;

use App\Enums\PageVisitKind;
use App\Models\User;
use App\Repositories\Contracts\IPageVisitRepository;
use App\Services\Contracts\IPageVisitService;
use App\Services\Contracts\IVisitorOriginService;
use Illuminate\Http\Request;

class PageVisitService implements IPageVisitService
{
    /**
     * @var list<string>
     */
    private const BOT_MARKERS = [
        'bot',
        'crawl',
        'spider',
        'slurp',
        'facebookexternalhit',
    ];

    public function __construct(
        private IPageVisitRepository $visits,
        private IVisitorOriginService $origin,
    ) {}

    public function recordPage(string $slug, ?Request $request = null): void
    {
        $request ??= request();

        if ($slug === '' || ! $this->shouldRecord($request)) {
            return;
        }

        $this->visits->create([
            'kind' => PageVisitKind::Page,
            'page_slug' => mb_substr($slug, 0, 191),
            'airtable_id' => null,
            ...$this->origin->captureVisit($request),
        ]);
    }

    public function recordProduct(?string $airtableId, ?Request $request = null): void
    {
        $request ??= request();
        $id = trim((string) $airtableId);

        if ($id === '' || ! $this->shouldRecord($request)) {
            return;
        }

        $this->visits->create([
            'kind' => PageVisitKind::Product,
            'page_slug' => null,
            'airtable_id' => mb_substr($id, 0, 32),
            ...$this->origin->captureVisit($request),
        ]);
    }

    private function shouldRecord(Request $request): bool
    {
        $user = $request->user();

        if ($user instanceof User && ($user->isAdmin() || $user->isStaff())) {
            return false;
        }

        return ! $this->isBot((string) $request->userAgent());
    }

    private function isBot(string $userAgent): bool
    {
        $userAgent = strtolower($userAgent);

        if ($userAgent === '') {
            return false;
        }

        foreach (self::BOT_MARKERS as $marker) {
            if (str_contains($userAgent, $marker)) {
                return true;
            }
        }

        return false;
    }
}
