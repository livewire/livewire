<?php

namespace Livewire\Mechanisms\HandleRequests;

class EndpointResolver
{
    /**
     * Get the concrete implementation from container
     */
    protected static function resolver(): EndpointResolverInterface
    {
        return app(EndpointResolverInterface::class);
    }

    /**
     * Get the base path prefix for all Livewire endpoints.
     *
     * Uses APP_KEY to generate a unique prefix per installation,
     * making it harder to target Livewire apps with universal scanners.
     */
    public static function prefix(): string
    {
        return static::resolver()->prefix();
    }

    /**
     * Get the path for the update endpoint.
     */
    public static function updatePath(): string
    {
        return static::resolver()->updatePath();
    }

    /**
     * Get the path for the JavaScript asset endpoint.
     */
    public static function scriptPath(bool $minified = false): string
    {
        return static::resolver()->scriptPath($minified);
    }

    /**
     * Get the path for the source map endpoint.
     */
    public static function mapPath(bool $csp = false): string
    {
        return static::resolver()->mapPath($csp);
    }

    /**
     * Get the path for the file upload endpoint.
     */
    public static function uploadPath(): string
    {
        return static::resolver()->uploadPath();
    }

    /**
     * Get the path for the file preview endpoint.
     */
    public static function previewPath(): string
    {
        return static::resolver()->previewPath();
    }

    /**
     * Get the path for component JavaScript modules.
     */
    public static function componentJsPath(): string
    {
        return static::resolver()->componentJsPath();
    }

    /**
     * Get the path for component CSS modules (scoped styles).
     */
    public static function componentCssPath(): string
    {
        return static::resolver()->componentCssPath();
    }

    /**
     * Get the path for component global CSS modules.
     */
    public static function componentGlobalCssPath(): string
    {
        return static::resolver()->componentGlobalCssPath();
    }
}
