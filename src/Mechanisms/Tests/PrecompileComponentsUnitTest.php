<?php

namespace Livewire\Mechanisms\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\WithConsoleEvents;
use Livewire\Livewire;

class PrecompileComponentsUnitTest extends \Tests\TestCase
{
    use WithConsoleEvents;

    public function test_view_cache_compiles_every_view_based_component_with_its_view_and_islands()
    {
        $path = realpath(__DIR__ . '/../../Features/SupportIslands/fixtures') . '/sfc-island-imports.blade.php';

        Livewire::addLocation(viewPath: dirname($path));

        Artisan::call('view:cache');

        $cacheManager = app('livewire.compiler')->cacheManager;

        $this->assertTrue($cacheManager->hasBeenCompiled($path));
        $this->assertFalse(Blade::isExpired($cacheManager->getViewPath($path)));

        $islands = File::files($cacheManager->cacheDirectory . '/islands');

        $this->assertNotEmpty($islands);

        foreach ($islands as $island) {
            $this->assertFalse(Blade::isExpired($island->getPathname()));
        }
    }
}
