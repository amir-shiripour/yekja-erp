<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;


class AssetInliner
{
    /** Process-level memo: avoids re-reading the same file twice in one request. */
    protected static array $runtime = [];

    /**
     * Full CSS text of the built application stylesheet, or '' when not built.
     */
    public static function appCss(): string
    {
        $path = self::resolveAppCssPath();
        if ($path === null) {
            return '';
        }

        return (string)self::remember('css', $path, static fn(string $file): string => (string)file_get_contents($file));
    }

    /**
     * `data:` URI for an image, or null when the file cannot be read.
     *
     * @param string|null $path Absolute path, or a path relative to the public disk.
     */
    public static function imageDataUri(?string $path): ?string
    {
        $absolute = self::resolveExistingPath($path);
        if ($absolute === null) {
            return null;
        }

        return self::remember('img', $absolute, static function (string $file): string {
            $mime = @mime_content_type($file) ?: 'image/png';

            return 'data:' . $mime . ';base64,' . base64_encode((string)file_get_contents($file));
        });
    }

    /**
     * Raw base64 payload of a font file, or '' when the file is missing.
     *
     * The caller supplies the `data:` prefix because the MIME type differs per
     * font format (ttf/woff/woff2) and is declared in the `@font-face` rule.
     */
    public static function fontBase64(string $path): string
    {
        if (!is_file($path)) {
            return '';
        }

        return (string)self::remember('font', $path, static fn(string $file): string => base64_encode((string)file_get_contents($file)));
    }

    /**
     * Locate the built app CSS through the Vite manifest (both layouts are checked
     * because Vite changed the manifest location between major versions).
     */
    protected static function resolveAppCssPath(): ?string
    {
        foreach ([public_path('build/manifest.json'), public_path('build/.vite/manifest.json')] as $manifestPath) {
            if (!is_file($manifestPath)) {
                continue;
            }

            $manifest = json_decode((string)file_get_contents($manifestPath), true);
            if (!is_array($manifest)) {
                continue;
            }

            foreach ($manifest as $entryKey => $entry) {
                if (str_contains((string)$entryKey, 'app.css') && isset($entry['file'])) {
                    $cssFile = public_path('build/' . $entry['file']);

                    return is_file($cssFile) ? $cssFile : null;
                }
            }
        }

        return null;
    }

    /**
     * Resolve a possibly-relative path to an absolute file that exists.
     */
    protected static function resolveExistingPath(?string $path): ?string
    {
        $path = trim((string)$path);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'data:')) {
            return null;
        }

        if (is_file($path)) {
            return $path;
        }

        $clean = ltrim($path, '/');
        foreach ([
                     public_path($clean),
                     storage_path('app/public/' . $clean),
                     public_path('storage/' . $clean),
                 ] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Cache a derived value keyed on the source file's mtime + size.
     */
    protected static function remember(string $kind, string $file, callable $resolver): ?string
    {
        $signature = $file . '|' . (@filemtime($file) ?: 0) . '|' . (@filesize($file) ?: 0);

        if (array_key_exists($signature, self::$runtime)) {
            return self::$runtime[$signature];
        }

        try {
            $value = Cache::remember(
                'resellerhub:asset:' . $kind . ':' . md5($signature),
                now()->addDay(),
                static fn(): string => (string)$resolver($file),
            );
        } catch (Throwable $e) {
            // A cache outage must never break invoice printing.
            Log::warning('resellerhub.asset_inliner.cache_failed', [
                'kind' => $kind,
                'error' => $e->getMessage(),
            ]);

            $value = (string)$resolver($file);
        }

        return self::$runtime[$signature] = $value;
    }
}
