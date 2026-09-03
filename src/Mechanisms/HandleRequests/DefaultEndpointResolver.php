<?php

namespace Livewire\Mechanisms\HandleRequests;

/**
 * Default implementation of the endpoint resolver.
 *
 * Delegates to the legacy EndpointResolver static API for backwards
 * compatibility while allowing the resolver to be overridden via the container.
 */
class DefaultEndpointResolver implements EndpointResolverInterface
{
    public function prefix(): string
    {
        return EndpointResolver::prefix();
    }

    public function updatePath(): string
    {
        return EndpointResolver::updatePath();
    }

    public function scriptPath(bool $minified = false): string
    {
        return EndpointResolver::scriptPath($minified);
    }

    public function mapPath(bool $csp = false): string
    {
        return EndpointResolver::mapPath($csp);
    }

    public function uploadPath(): string
    {
        return EndpointResolver::uploadPath();
    }

    public function previewPath(): string
    {
        return EndpointResolver::previewPath();
    }

    public function componentJsPath(): string
    {
        return EndpointResolver::componentJsPath();
    }

    public function componentCssPath(): string
    {
        return EndpointResolver::componentCssPath();
    }

    public function componentGlobalCssPath(): string
    {
        return EndpointResolver::componentGlobalCssPath();
    }
}
