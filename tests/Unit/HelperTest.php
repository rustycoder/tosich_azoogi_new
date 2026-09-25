<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function test_accent_html_without_braces_escapes_and_formats_newlines(): void
    {
        $input = "Hello World\nLine 2";
        $expected = 'Hello World<br>Line 2';

        $this->assertSame($expected, accent_html($input));
    }

    public function test_accent_html_converts_braces_to_span_tag(): void
    {
        $input = "Engineered Lighting.\nInfinite Scale.\n{Zero Compromise.}";
        $expected = 'Engineered Lighting.<br>Infinite Scale.<br><span class="accent">Zero Compromise.</span>';

        $this->assertSame($expected, accent_html($input));
    }

    public function test_accent_html_supports_multiple_braces_in_single_string(): void
    {
        $input = '{Precision} Lighting with {Custom} Control';
        $expected = '<span class="accent">Precision</span> Lighting with <span class="accent">Custom</span> Control';

        $this->assertSame($expected, accent_html($input));
    }

    public function test_accent_html_escapes_special_html_characters(): void
    {
        $input = 'Lighting <Design> & {Engineering & Innovation}';
        $expected = 'Lighting &lt;Design&gt; &amp; <span class="accent">Engineering &amp; Innovation</span>';

        $this->assertSame($expected, accent_html($input));
    }
}
