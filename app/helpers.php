<?php

use App\Support\PageMetaBag;

if (! function_exists('versioned_asset')) {
    /**
     * Public asset URL with a filemtime query so browsers fetch CSS/JS after each save.
     */
    function versioned_asset(string $path): string
    {
        $path = ltrim($path, '/');
        $fullPath = public_path($path);
        $version = is_file($fullPath)
            ? (string) filemtime($fullPath)
            : (string) config('app.asset_version');

        return asset($path).'?v='.$version;
    }
}

if (! function_exists('cms_section_attr')) {
    function cms_section_attr(string $section): string
    {
        if (! request()->routeIs('dashboard.pages.preview')) {
            return '';
        }

        return 'data-cms-section="'.e($section).'"';
    }
}

if (! function_exists('cms_style')) {
    function cms_style(?PageMetaBag $meta, string $key, int $sortOrder = 0, string $extra = ''): string
    {
        return $meta?->style($key, $sortOrder, $extra) ?? ($extra === '' ? '' : ' style="'.e($extra).'"');
    }
}

if (! function_exists('tel_href')) {
    function tel_href(string $phone): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $phone);
    }
}

if (! function_exists('chrome_url')) {
    function chrome_url(string $href): string
    {
        $href = trim($href);

        if ($href === '') {
            return '#';
        }

        if (
            str_starts_with($href, 'http://')
            || str_starts_with($href, 'https://')
            || str_starts_with($href, 'mailto:')
            || str_starts_with($href, 'tel:')
            || str_starts_with($href, '#')
        ) {
            return $href;
        }

        return url($href);
    }
}

if (! function_exists('chrome_target_attrs')) {
    function chrome_target_attrs(?string $target): string
    {
        if ($target !== '_blank') {
            return '';
        }

        return ' target="_blank" rel="noopener noreferrer"';
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

if (! function_exists('dash_search_query')) {
    function dash_search_query(mixed $value = null): string
    {
        $value ??= request()->query('q');

        return mb_substr(trim((string) $value), 0, 80);
    }
}
