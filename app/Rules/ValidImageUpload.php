<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class ValidImageUpload implements Rule
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'jpe', 'jfif', 'png', 'gif', 'webp', 'bmp'];

    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
    ];

    public function passes($attribute, $value)
    {
        if (!$value instanceof UploadedFile || !$value->isValid()) {
            return false;
        }

        $extension = strtolower((string) $value->getClientOriginalExtension());
        if ($extension !== '' && !in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return false;
        }

        $path = $value->getRealPath();
        if (!$path || !is_file($path)) {
            return false;
        }

        $detectedMime = $this->detectMimeType($path);
        if ($detectedMime === null || !in_array($detectedMime, self::ALLOWED_MIMES, true)) {
            return false;
        }

        $binary = @file_get_contents($path, false, null, 0, 32);
        if ($binary === false || !$this->matchesMagicNumber($binary, $detectedMime)) {
            return false;
        }

        $imageInfo = @getimagesize($path);
        if ($imageInfo !== false && !empty($imageInfo['mime']) && strtolower($imageInfo['mime']) !== $detectedMime) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'El archivo debe ser una imagen válida (jpg, jpeg, png, gif, webp o bmp) y pasar la verificación de contenido.';
    }

    private function detectMimeType(string $path): ?string
    {
        $info = @getimagesize($path);
        if ($info !== false && !empty($info['mime'])) {
            return strtolower((string) $info['mime']);
        }

        if (!function_exists('finfo_open')) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }

        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);

        return $mimeType ? strtolower($mimeType) : null;
    }

    private function matchesMagicNumber(string $binary, string $mimeType): bool
    {
        $bytes = array_map('ord', str_split($binary));

        switch ($mimeType) {
            case 'image/jpeg':
                return isset($bytes[0], $bytes[1], $bytes[2])
                    && $bytes[0] === 0xFF
                    && $bytes[1] === 0xD8
                    && $bytes[2] === 0xFF;

            case 'image/png':
                return $this->startsWith($bytes, [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A]);

            case 'image/gif':
                return $this->startsWith($bytes, [0x47, 0x49, 0x46, 0x38])
                    && isset($bytes[4], $bytes[5])
                    && (($bytes[4] === 0x37 && $bytes[5] === 0x61) || ($bytes[4] === 0x39 && $bytes[5] === 0x61));

            case 'image/webp':
                return $this->startsWith($bytes, [0x52, 0x49, 0x46, 0x46])
                    && isset($bytes[8], $bytes[9], $bytes[10], $bytes[11])
                    && $bytes[8] === 0x57
                    && $bytes[9] === 0x45
                    && $bytes[10] === 0x42
                    && $bytes[11] === 0x50;

            case 'image/bmp':
                return $this->startsWith($bytes, [0x42, 0x4D]);

            default:
                return false;
        }
    }

    private function startsWith(array $bytes, array $signature): bool
    {
        if (count($bytes) < count($signature)) {
            return false;
        }

        foreach ($signature as $index => $expectedByte) {
            if (!isset($bytes[$index]) || $bytes[$index] !== $expectedByte) {
                return false;
            }
        }

        return true;
    }
}