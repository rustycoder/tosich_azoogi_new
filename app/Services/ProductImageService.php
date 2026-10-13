<?php

namespace App\Services;

use App\Services\Contracts\IProductImageService;
use GdImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProductImageService implements IProductImageService
{
    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? storage_path('framework/cache/product-images');
    }

    /**
     * @return array{path: string, mime: string, size: int, etag: string, last_modified: int}|null
     */
    public function deliver(string $sourceUrl, string $format): ?array
    {
        $targetFormat = strtolower($format);
        if ($targetFormat === 'jpeg') {
            $targetFormat = 'jpg';
        }

        if (! in_array($targetFormat, ['jpg', 'png', 'webp'], true)) {
            return null;
        }

        if (! is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }

        $cacheKey = sha1($sourceUrl.'|'.$targetFormat);
        $cachePath = $this->cacheDir.'/'.$cacheKey.'.'.$targetFormat;

        if (file_exists($cachePath) && filesize($cachePath) > 0) {
            return $this->formatPayload($cachePath, $targetFormat);
        }

        $sourceBinary = $this->fetchSourceBinary($sourceUrl);
        if ($sourceBinary === null || $sourceBinary === '') {
            return null;
        }

        $tempPath = $this->cacheDir.'/'.uniqid('tmp_', true).'.'.$targetFormat;

        try {
            $detectedFormat = $this->detectFormat($sourceBinary);

            if ($detectedFormat === $targetFormat) {
                file_put_contents($tempPath, $sourceBinary);
            } else {
                $converted = match ($targetFormat) {
                    'jpg' => $this->convertToJpeg($sourceBinary, $tempPath),
                    'png' => $this->convertToPng($sourceBinary, $tempPath),
                    'webp' => $this->convertToWebp($sourceBinary, $tempPath),
                };

                if (! $converted || ! file_exists($tempPath) || filesize($tempPath) === 0) {
                    @unlink($tempPath);

                    return null;
                }
            }

            @rename($tempPath, $cachePath);

            return $this->formatPayload($cachePath, $targetFormat);
        } catch (Throwable $e) {
            @unlink($tempPath);
            Log::warning('ProductImageService conversion error: '.$e->getMessage(), [
                'sourceUrl' => $sourceUrl,
                'targetFormat' => $targetFormat,
            ]);

            return null;
        }
    }

    private function fetchSourceBinary(string $sourceUrl): ?string
    {
        if (str_starts_with($sourceUrl, 'http://') || str_starts_with($sourceUrl, 'https://')) {
            try {
                $response = Http::timeout(10)->get($sourceUrl);
                if ($response->successful()) {
                    return $response->body();
                }
            } catch (Throwable $e) {
                Log::warning('Failed to fetch remote product image: '.$e->getMessage(), ['url' => $sourceUrl]);
            }

            return null;
        }

        $cleaned = ltrim(parse_url($sourceUrl, PHP_URL_PATH) ?? $sourceUrl, '/');
        $possiblePaths = [
            public_path($cleaned),
            base_path($cleaned),
            storage_path('app/public/'.$cleaned),
        ];

        foreach ($possiblePaths as $path) {
            if (is_file($path) && is_readable($path)) {
                return file_get_contents($path) ?: null;
            }
        }

        return null;
    }

    private function detectFormat(string $binary): ?string
    {
        if (str_starts_with($binary, "\xFF\xD8\xFF")) {
            return 'jpg';
        }

        if (str_starts_with($binary, "\x89PNG\r\n\x1a\n")) {
            return 'png';
        }

        if (str_starts_with($binary, 'RIFF') && str_contains(substr($binary, 8, 8), 'WEBP')) {
            return 'webp';
        }

        if (str_starts_with($binary, 'GIF8')) {
            return 'gif';
        }

        return null;
    }

    private function convertToJpeg(string $binary, string $targetPath): bool
    {
        $source = @imagecreatefromstring($binary);
        if (! $source instanceof GdImage) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        $canvas = imagecreatetruecolor($width, $height);
        if (! $canvas instanceof GdImage) {
            imagedestroy($source);

            return false;
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        $success = imagejpeg($canvas, $targetPath, 92);

        imagedestroy($canvas);
        imagedestroy($source);

        return $success;
    }

    private function convertToPng(string $binary, string $targetPath): bool
    {
        $source = @imagecreatefromstring($binary);
        if (! $source instanceof GdImage) {
            return false;
        }

        imagealphablending($source, false);
        imagesavealpha($source, true);

        $success = imagepng($source, $targetPath, 6);

        imagedestroy($source);

        return $success;
    }

    private function convertToWebp(string $binary, string $targetPath): bool
    {
        $source = @imagecreatefromstring($binary);
        if (! $source instanceof GdImage) {
            return false;
        }

        imagealphablending($source, false);
        imagesavealpha($source, true);

        $success = imagewebp($source, $targetPath, 90);

        imagedestroy($source);

        return $success;
    }

    /**
     * @return array{path: string, mime: string, size: int, etag: string, last_modified: int}
     */
    private function formatPayload(string $path, string $format): array
    {
        $mime = match ($format) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return [
            'path' => $path,
            'mime' => $mime,
            'size' => (int) filesize($path),
            'etag' => '"'.sha1_file($path).'"',
            'last_modified' => (int) filemtime($path),
        ];
    }
}
