<?php

namespace Livewire\Mechanisms\HandleRequests;

class EndpointResolver
{
    /**
     * Get the base path prefix for all Livewire endpoints.
     *
     * Uses APP_KEY to generate a unique prefix per installation,
     * making it harder to target Livewire apps with universal scanners.
     */
    public static function prefix(): string
    {
        $hash = substr(hash('sha256', config('app.key') . 'livewire-endpoint'), 0, 8);

        return '/livewire-' . $hash;
    }

    /**
     * Get the path for the update endpoint.
     */
    public static function updatePath(): string
    {
        return static::prefix() . '/update';
    }

    /**
     * Get the path for the JavaScript asset endpoint.
     */
    public static function scriptPath(bool $minified = false): string
    {
        $file = $minified ? 'livewire.min.js' : 'livewire.js';

        return static::prefix() . '/' . $file;
    }

    /**
     * Get the path for the source map endpoint.
     */
    public static function mapPath(bool $csp = false): string
    {
        $file = $csp ? 'livewire.csp.min.js.map' : 'livewire.min.js.map';

        return static::prefix() . '/' . $file;
    }

    /**
     * Get the path for the file upload endpoint.
     */
    public static function uploadPath(): string
    {
        return static::prefix() . '/upload-file';
    }

    /**
     * Get the path for the file preview endpoint.
     */
    public static function previewPath(): string
    {
        return static::prefix() . '/preview-file/{filename}';
    }

    /**
     * Get the path for the chunked upload init endpoint.
     */
    public static function chunkInitPath(): string
    {
        return static::prefix() . '/chunk-upload';
    }

    /**
     * Get the path for the chunked upload patch endpoint.
     */
    public static function chunkPatchPath(): string
    {
        return static::prefix() . '/chunk-upload/{transferId}';
    }

    /**
     * Get the path for the chunked upload offset check endpoint.
     */
    public static function chunkOffsetPath(): string
    {
        return static::prefix() . '/chunk-upload/{transferId}/offset';
    }

    public static function s3MultipartInitPath(): string
    {
        return static::prefix() . '/s3-multipart';
    }

    public static function s3MultipartSignPartPath(): string
    {
        return static::prefix() . '/s3-multipart/{uploadId}/sign-part';
    }

    public static function s3MultipartCompletePath(): string
    {
        return static::prefix() . '/s3-multipart/{uploadId}/complete';
    }

    public static function s3MultipartAbortPath(): string
    {
        return static::prefix() . '/s3-multipart/{uploadId}/abort';
    }

    public static function s3MultipartListPartsPath(): string
    {
        return static::prefix() . '/s3-multipart/{uploadId}/list-parts';
    }

    /**
     * Get the path for component JavaScript modules.
     */
    public static function componentJsPath(): string
    {
        return static::prefix() . '/js/{component}.js';
    }

    /**
     * Get the path for component CSS modules (scoped styles).
     */
    public static function componentCssPath(): string
    {
        return static::prefix() . '/css/{component}.css';
    }

    /**
     * Get the path for component global CSS modules.
     */
    public static function componentGlobalCssPath(): string
    {
        return static::prefix() . '/css/{component}.global.css';
    }
}
