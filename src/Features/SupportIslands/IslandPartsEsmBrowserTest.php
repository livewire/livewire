<?php

namespace Livewire\Features\SupportIslands;

use Illuminate\Support\Facades\Route;
use Livewire\Drawer\Utils;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;

class IslandPartsEsmBrowserTest extends IslandPartsBrowserTest
{
    protected static $csp = false;

    public static function tweakApplicationHook()
    {
        $csp = static::$csp;

        return function () use ($csp) {
            config(['app.debug' => true, 'livewire.csp_safe' => $csp]);
            $assets = app(FrontendAssets::class);
            $assets->useScriptTagAttributes(['type' => 'module']);
            $assets->setScriptRoute(function ($handle, $path) use ($csp) {
                return Route::get($path, function () use ($csp) {
                    $file = $csp ? 'livewire.csp.esm.js' : 'livewire.esm.js';
                    return Utils::pretendResponseIsFile(__DIR__ . '/../../../dist/' . $file);
                });
            });
        };
    }
}
