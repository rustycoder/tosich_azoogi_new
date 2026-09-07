<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Contracts\ILedCalculatorService;
use Illuminate\View\View;

class LedCalculatorController extends Controller
{
    public function __construct(private ILedCalculatorService $calculator) {}

    public function __invoke(): View
    {
        return view('pages.led-strip-calculator', [
            'calculatorCatalog' => $this->calculator->catalog(),
        ]);
    }
}
