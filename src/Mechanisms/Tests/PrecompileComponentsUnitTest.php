<?php

namespace Livewire\Mechanisms\Tests;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class PrecompileComponentsUnitTest extends \Tests\TestCase
{
    public function test_view_cache_compiles_every_view_based_component_with_its_view_and_islands()
    {
        $path = realpath(__DIR__ . '/../../Features/SupportIslands/Fixtures') . '/sfc-island-imports.blade.php';

        Livewire::addLocation(viewPath: dirname($path));

        Artisan::call('view:cache');

        // Artisan fires command events only from the console kernel, so fire the one a terminal would...
        event(new CommandFinished('view:cache', new ArrayInput([]), new NullOutput, 0));

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
