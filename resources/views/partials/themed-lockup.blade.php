@php
    $svg = is_file(public_path($path)) ? file_get_contents(public_path($path)) : false;

    if ($svg !== false) {
        $svg = str_replace('fill="'.$fill.'"', 'fill="currentColor"', $svg);
        $svg = preg_replace('/<svg\b/', '<svg class="'.e($class).'"', $svg, 1) ?? $svg;

        if (! str_contains($svg, 'aria-label')) {
            $svg = preg_replace(
                '/<svg class="'.preg_quote(e($class), '/').'"/',
                '<svg class="'.e($class).'" role="img" aria-label="'.e($label).'"',
                $svg,
                1,
            ) ?? $svg;
        }
    } else {
        $svg = '';
    }
@endphp
{!! $svg !!}
