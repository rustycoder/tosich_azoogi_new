<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Chat\AiCostCalculator;
use PHPUnit\Framework\TestCase;

class AiCostCalculatorTest extends TestCase
{
    public function test_it_calculates_claude_3_5_sonnet_cost_correctly(): void
    {
        // 1,000 prompt tokens @ $3.00/1M = $0.003
        // 500 completion tokens @ $15.00/1M = $0.0075
        // Total = $0.0105
        $cost = AiCostCalculator::calculate('claude-3-5-sonnet-20241022', 1000, 500);

        $this->assertEquals(0.0105, $cost);
    }

    public function test_it_calculates_claude_3_5_haiku_cost_correctly(): void
    {
        // 10,000 prompt tokens @ $0.80/1M = $0.008
        // 2,000 completion tokens @ $4.00/1M = $0.008
        // Total = $0.016
        $cost = AiCostCalculator::calculate('claude-3-5-haiku-20241022', 10000, 2000);

        $this->assertEquals(0.016, $cost);
    }

    public function test_it_calculates_gemini_2_5_flash_cost_correctly(): void
    {
        // 100,000 prompt tokens @ $0.15/1M = $0.015
        // 50,000 completion tokens @ $0.60/1M = $0.030
        // Total = $0.045
        $cost = AiCostCalculator::calculate('gemini-2.5-flash', 100000, 50000);

        $this->assertEquals(0.045, $cost);
    }

    public function test_it_calculates_gpt_4o_mini_cost_correctly(): void
    {
        // 20,000 prompt tokens @ $0.15/1M = $0.003
        // 10,000 completion tokens @ $0.60/1M = $0.006
        // Total = $0.009
        $cost = AiCostCalculator::calculate('gpt-4o-mini', 20000, 10000);

        $this->assertEquals(0.009, $cost);
    }

    public function test_it_calculates_deepseek_cost_correctly(): void
    {
        // 100,000 prompt tokens @ $0.14/1M = $0.014
        // 50,000 completion tokens @ $0.28/1M = $0.014
        // Total = $0.028
        $cost = AiCostCalculator::calculate('deepseek-chat', 100000, 50000);

        $this->assertEquals(0.028, $cost);
    }

    public function test_it_handles_zero_tokens_and_null_model(): void
    {
        $cost = AiCostCalculator::calculate(null, 0, 0);

        $this->assertEquals(0.0, $cost);
    }
}
