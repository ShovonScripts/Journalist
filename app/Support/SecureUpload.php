<?php

namespace App\Support;

use RuntimeException;

/**
 * The single media-processing pipeline (ADMIN_PANEL.md §9, SECURITY.md §10–§11).
 * The MIME type is sniffed from the bytes — never the extension or the
 * Content-Type header — and images are re-encoded through GD, which strips
 * EXIF (including source-photo geolocation) and bounds the dimensions, emitting
 * WebP when that re-encode is smaller. The URL-import path (a later phase) runs
 * through this same class, so an imported image is byte-equivalent to an
 * uploaded one.
 */
class SecureUpload
{
    public const MAX_BYTES = 10 * 1024 * 1024;

    public const MAX_DIMENSION = 2560;

    /** @var list<string> */
    public const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /** @var list<string> */
    public const DOCUMENT_MIMES = ['application/pdf'];

    /**
     * @return array{contents: string, extension: string, mime: string, width: ?int, height: ?int}
     */
    public static function sanitize(string $path): array
    {
        $mime = self::sniffMime($path);

        if (in_array($mime, self::IMAGE_MIMES, true)) {
            return self::sanitizeImage($path);
        }

        if (in_array($mime, self::DOCUMENT_MIMES, true)) {
            return self::sanitizeDocument($path);
        }

        throw new RuntimeException('Unsupported file type: '.$mime);
    }

    public static function sniffMime(string $path): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($path) ?: 'application/octet-stream';
    }

    /**
     * Whether the bytes at the given path are one of the types the pipeline can
     * accept. Lets validation reject a spoofed file before sanitize() runs.
     */
    public static function accepts(string $path): bool
    {
        $mime = self::sniffMime($path);

        return in_array($mime, self::IMAGE_MIMES, true)
            || in_array($mime, self::DOCUMENT_MIMES, true);
    }

    /**
     * @return array{contents: string, extension: string, mime: string, width: ?int, height: ?int}
     */
    private static function sanitizeImage(string $path): array
    {
        $data = (string) file_get_contents($path);

        $image = @imagecreatefromstring($data);

        if ($image === false) {
            throw new RuntimeException('The image could not be decoded.');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $scale = min(1, self::MAX_DIMENSION / max($width, $height));

        if ($scale < 1) {
            $resized = imagescale(
                $image,
                max(1, (int) round($width * $scale)),
                max(1, (int) round($height * $scale)),
            );

            if ($resized !== false) {
                imagedestroy($image);
                $image = $resized;
                $width = imagesx($image);
                $height = imagesy($image);
            }
        }

        $webp = self::encode($image, 'webp');
        $jpeg = self::encode($image, 'jpeg');
        imagedestroy($image);

        if ($webp !== null && strlen($webp) <= strlen($jpeg)) {
            return [
                'contents' => $webp,
                'extension' => 'webp',
                'mime' => 'image/webp',
                'width' => $width,
                'height' => $height,
            ];
        }

        return [
            'contents' => $jpeg ?? $webp ?? '',
            'extension' => 'jpg',
            'mime' => 'image/jpeg',
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * @return array{contents: string, extension: string, mime: string, width: ?int, height: ?int}
     */
    private static function sanitizeDocument(string $path): array
    {
        return [
            'contents' => (string) file_get_contents($path),
            'extension' => 'pdf',
            'mime' => 'application/pdf',
            'width' => null,
            'height' => null,
        ];
    }

    private static function encode(\GdImage $image, string $format): ?string
    {
        ob_start();

        $ok = match ($format) {
            'webp' => function_exists('imagewebp') && imagewebp($image, null, 82),
            'jpeg' => imagejpeg($image, null, 85),
            default => false,
        };

        $buffer = ob_get_clean();

        return $ok && is_string($buffer) ? $buffer : null;
    }
}
