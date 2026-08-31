<?php

if (! function_exists('cms_section_attr')) {
    function cms_section_attr(string $section): string
    {
        if (! request()->routeIs('dashboard.pages.preview')) {
            return '';
        }

        return 'data-cms-section="'.e($section).'"';
    }
}

if (! function_exists('media_url')) {
    function media_url(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return implode('/', array_map(
            fn (string $part): string => $part === '' ? '' : rawurlencode($part),
            explode('/', $path),
        ));
    }
}

if (! function_exists('accent_html')) {
    function accent_html(string $text, string $accent = ''): string
    {
        if ($accent === '') {
            return e($text);
        }

        $position = strpos($text, $accent);

        if ($position === false) {
            return e($text);
        }

        return e(substr($text, 0, $position))
            .'<span>'.e($accent).'</span>'
            .e(substr($text, $position + strlen($accent)));
    }
}

if (! function_exists('nl2br_html')) {
    function nl2br_html(string $text, bool $spanLast = false): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $text) ?: [];
        $lines = array_map(fn (string $line): string => e($line), $lines);

        if ($spanLast && count($lines) > 1) {
            $last = array_pop($lines);
            $lines[] = '<span>'.$last.'</span>';
        }

        return implode('<br>', $lines);
    }
}

if (! function_exists('linkify_emails')) {
    function linkify_emails(string $text): string
    {
        $escaped = e($text);
        $linked = preg_replace(
            '/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/',
            '<a href="mailto:$1">$1</a>',
            $escaped,
        );

        return $linked ?: $escaped;
    }
}

if (! function_exists('labelled_tick')) {
    function labelled_tick(string $text): string
    {
        $colon = strpos($text, ':');

        if ($colon === false) {
            return e($text);
        }

        return '<strong>'.e(substr($text, 0, $colon + 1)).'</strong>'.e(substr($text, $colon + 1));
    }
}
