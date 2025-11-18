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
     * Get the path for component JavaScript modules.
     */
    public static function componentJsPath(): string
    {
        return static::prefix() . '/js/{component}.js';
    }
}
