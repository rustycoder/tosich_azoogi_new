<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IProductSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function __construct(private IProductSyncService $products) {}

    public function index(Request $request): View
    {
        $search = dash_search_query($request->query('q'));

        return view('dashboard.products.index', [
            'products' => $this->products->dashboardList($search),
            'search' => $search,
            'latestSync' => $this->products->latestSync(),
        ]);
    }

    public function sync(): RedirectResponse
    {
        $this->products->dispatch((string) auth()->id());

        return redirect()
            ->route('dashboard.products.index')
            ->with('status', 'Product sync started. Refresh in a moment to see updates.');
    }

    public function syncStream(Request $request): StreamedResponse
    {
        set_time_limit(1200);

        return response()->stream(function (): void {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $sendEvent = function (array $data): void {
                echo 'data: '.json_encode($data)."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            };

            try {
                $this->products->sync((string) auth()->id(), function (array $event) use ($sendEvent): void {
                    $sendEvent($event);
                });
            } catch (\Throwable $e) {
                $sendEvent([
                    'status' => 'failed',
                    'percentage' => 0,
                    'step' => 'Sync failed',
                    'log' => 'Error: '.$e->getMessage(),
                    'error' => $e->getMessage(),
                    'time' => now()->format('H:i:s'),
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
