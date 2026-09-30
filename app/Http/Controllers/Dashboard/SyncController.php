<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Contracts\ICatalogAuditService;
use App\Services\Contracts\IProductSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(
        private readonly ICatalogAuditService $auditService,
        private readonly IProductSyncService $syncService,
    ) {}

    public function index(Request $request): View
    {
        $audit = $this->auditService->audit();
        $latestSync = $this->syncService->latestSync();

        return view('dashboard.sync.index', [
            'audit' => $audit,
            'latestSync' => $latestSync,
        ]);
    }

    public function sync(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $this->syncService->sync(triggeredBy: $user ? 'user:'.$user->name : 'dashboard');

            return redirect()
                ->route('dashboard.sync.index')
                ->with('status', 'Product catalog synchronized successfully with Airtable.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('dashboard.sync.index')
                ->with('error', 'Airtable sync failed: '.$e->getMessage());
        }
    }
}
