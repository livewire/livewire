<?php

namespace Livewire\Mechanisms\HandleRequests;

interface EndpointResolverInterface
{
    public function prefix(): string;

    public function updatePath(): string;

    public function scriptPath(bool $minified = false): string;

    public function mapPath(bool $csp = false): string;

    public function uploadPath(): string;

    public function previewPath(): string;

    public function componentJsPath(): string;

    public function componentCssPath(): string;

    public function componentGlobalCssPath(): string;
}