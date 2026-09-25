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

if (! function_exists('video_mime_type')) {
    function video_mime_type(?string $path): string
    {
        if ($path === null || $path === '') {
            return 'video/mp4';
        }

        $parsedPath = parse_url($path, PHP_URL_PATH);
        $ext = strtolower(pathinfo(is_string($parsedPath) ? $parsedPath : $path, PATHINFO_EXTENSION));

        return match ($ext) {
            'webm' => 'video/webm',
            'ogg', 'ogv' => 'video/ogg',
            'mov', 'qt' => 'video/quicktime',
            'm4v' => 'video/x-m4v',
            default => 'video/mp4',
        };
    }
}

if (! function_exists('media_file_info')) {
    /**
     * @return array{name: string, format: string, size: string, dimensions: string, aspect_ratio: string}|null
     */
    function media_file_info(?string $path): ?array
    {
        if ($path === null || $path === '') {
            return null;
        }

        $cleanPath = ltrim(parse_url($path, PHP_URL_PATH) ?? $path, '/');
        $fullPath = public_path($cleanPath);

        $name = basename($cleanPath);
        $ext = strtoupper(pathinfo($cleanPath, PATHINFO_EXTENSION) ?: 'IMAGE');
        $formattedSize = '';
        $dimensions = '';
        $aspectRatio = '';

        if (file_exists($fullPath) && ! is_dir($fullPath)) {
            $bytes = filesize($fullPath);
            if ($bytes !== false) {
                if ($bytes >= 1048576) {
                    $formattedSize = number_format($bytes / 1048576, 1).' MB';
                } elseif ($bytes >= 1024) {
                    $formattedSize = number_format($bytes / 1024, 1).' KB';
                } else {
                    $formattedSize = $bytes.' B';
                }
            }

            $imgSize = @getimagesize($fullPath);
            if ($imgSize && isset($imgSize[0], $imgSize[1])) {
                $w = $imgSize[0];
                $h = $imgSize[1];
                $dimensions = "{$w} × {$h} px";

                $ratio = $h > 0 ? $w / $h : 0;
                if (abs($ratio - 16 / 9) < 0.03) {
                    $aspectRatio = '16:9';
                } elseif (abs($ratio - 4 / 3) < 0.03) {
                    $aspectRatio = '4:3';
                } elseif (abs($ratio - 3 / 2) < 0.03) {
                    $aspectRatio = '3:2';
                } elseif (abs($ratio - 1 / 1) < 0.02) {
                    $aspectRatio = '1:1';
                } elseif (abs($ratio - 21 / 9) < 0.03) {
                    $aspectRatio = '21:9';
                } elseif (abs($ratio - 9 / 16) < 0.03) {
                    $aspectRatio = '9:16';
                } elseif ($ratio > 0) {
                    $aspectRatio = number_format($ratio, 2).':1';
                }
            }
        }

        return [
            'name' => $name,
            'format' => $ext,
            'size' => $formattedSize,
            'dimensions' => $dimensions,
            'aspect_ratio' => $aspectRatio,
        ];
    }
}

if (! function_exists('accent_html')) {
    function accent_html(string $text): string
    {
        $escaped = e($text);
        $html = preg_replace('/\{([^}]+)\}/', '<span>$1</span>', $escaped);

        return str_replace(["\r\n", "\n", "\r"], '<br>', $html ?? $escaped);
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

        $result = implode('<br>', $lines);
        $html = preg_replace('/\{([^}]+)\}/', '<span>$1</span>', $result);

        return $html ?? $result;
    }
}

if (! function_exists('linkify_emails')) {
    function linkify_emails(string $text): string
    {
        $isHtml = str_contains($text, '<span') || str_contains($text, '<br');
        $escaped = $isHtml ? $text : e($text);
        $linked = preg_replace(
            '/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})(?![^<]*>|[^<>]*<\/a>)/',
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
            return accent_html($text);
        }

        $label = substr($text, 0, $colon + 1);
        $rest = substr($text, $colon + 1);

        return '<strong>'.accent_html($label).'</strong>'.accent_html($rest);
    }
}

if (! function_exists('country_name')) {
    function country_name(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '' || strlen($code) !== 2 || ! ctype_alpha($code)) {
            return '';
        }

        if (class_exists(Locale::class)) {
            $name = Locale::getDisplayRegion('-'.$code, 'en');

            if (is_string($name) && $name !== '' && strtoupper($name) !== $code) {
                return $name;
            }
        }

        return $code;
    }
}

if (! function_exists('device_name')) {
    function device_name(?string $userAgent): string
    {
        $ua = trim((string) $userAgent);

        if ($ua === '') {
            return '';
        }

        $os = match (true) {
            str_contains($ua, 'iPhone') => 'iPhone',
            str_contains($ua, 'iPad') => 'iPad',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS X'), str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => '',
        };

        $browser = match (true) {
            str_contains($ua, 'Edg/') || str_contains($ua, 'EdgA/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome/') || str_contains($ua, 'CriOS/') => 'Chrome',
            str_contains($ua, 'Firefox/') || str_contains($ua, 'FxiOS/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari',
            default => '',
        };

        if ($browser !== '' && $os !== '') {
            return $browser.' on '.$os;
        }

        return $browser !== '' ? $browser : ($os !== '' ? $os : $ua);
    }
}

if (! function_exists('dash_search_query')) {
    function dash_search_query(mixed $value = null): string
    {
        $value ??= request()->query('q');

        return mb_substr(trim((string) $value), 0, 80);
    }
}
