<?php

namespace Tests\Unit;

use App\PageMeta\Typography;
use PHPUnit\Framework\TestCase;

class TypographyTest extends TestCase
{
    public function test_cms_sizes_allow_hero_scale_and_cap_the_rest(): void
    {
        $this->assertSame([
            '10px' => '10px',
            '12px' => '12px',
            '14px' => '14px',
            '16px' => '16px',
            '18px' => '18px',
            '20px' => '20px',
            '22px' => '22px',
            '24px' => '24px',
            '28px' => '28px',
            '36px' => '36px',
            '42px' => '42px',
            '48px' => '48px',
            '56px' => '56px',
            '64px' => '64px',
        ], Typography::sizes());
    }

    public function test_sizes_between_section_and_hero_are_not_offered(): void
    {
        $this->assertNull(Typography::size('32px'));
        $this->assertSame('28px', Typography::size('28px'));
        $this->assertSame('36px', Typography::size('36px'));
        $this->assertSame('64px', Typography::size('64px'));
    }
}
