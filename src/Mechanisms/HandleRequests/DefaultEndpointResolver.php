<?php

namespace Livewire\Mechanisms\HandleRequests;

/**
 * Default implementation of the endpoint resolver.
 */
class DefaultEndpointResolver implements EndpointResolverInterface
{
    /**
     * Get the base path prefix for all Livewire endpoints.
     *
     * Uses APP_KEY to generate a unique prefix per installation,
     * making it harder to target Livewire apps with universal scanners.
     */
    public function prefix(): string
    {
        $hash = substr(hash('sha256', config('app.key') . 'livewire-endpoint'), 0, 8);

        return '/livewire-' . $hash;
    }

    /**
     * Get the path for the update endpoint.
     */
    public function updatePath(): string
    {
        return $this->prefix() . '/update';
    }

    /**
     * Get the path for the JavaScript asset endpoint.
     */
    public function scriptPath(bool $minified = false): string
    {
        $file = $minified ? 'livewire.min.js' : 'livewire.js';

        return $this->prefix() . '/' . $file;
    }

    /**
     * Get the path for the source map endpoint.
     */
    public function mapPath(bool $csp = false): string
    {
        $file = $csp ? 'livewire.csp.min.js.map' : 'livewire.min.js.map';

        return $this->prefix() . '/' . $file;
    }

    /**
     * Get the path for the file upload endpoint.
     */
    public function uploadPath(): string
    {
        return $this->prefix() . '/upload-file';
    }

    /**
     * Get the path for the file preview endpoint.
     */
    public function previewPath(): string
    {
        return $this->prefix() . '/preview-file/{filename}';
    }

    /**
     * Get the path for component JavaScript modules.
     */
    public function componentJsPath(): string
    {
        return $this->prefix() . '/js/{component}.js';
    }

    /**
     * Get the path for component CSS modules (scoped styles).
     */
    public function componentCssPath(): string
    {
        return $this->prefix() . '/css/{component}.css';
    }

    /**
     * Get the path for component global CSS modules.
     */
    public function componentGlobalCssPath(): string
    {
        return $this->prefix() . '/css/{component}.global.css';
    }
}
