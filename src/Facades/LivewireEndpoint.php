<?php

namespace Livewire\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string prefix()
 * @method static string updatePath()
 * @method static string scriptPath(bool $minified = false)
 * @method static string mapPath(bool $csp = false)
 * @method static string uploadPath()
 * @method static string previewPath()
 * @method static string componentJsPath()
 * @method static string componentCssPath()
 * @method static string componentGlobalCssPath()
 * 
 * @see \Livewire\Mechanisms\HandleRequests\EndpointResolver
 */
class LivewireEndpoint extends Facade
{
    public static function getFacadeAccessor()
    {
        return \Livewire\Mechanisms\HandleRequests\EndpointResolverInterface::class;
    }
}