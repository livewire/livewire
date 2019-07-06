<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public static $viewCacheCleared = false;

    public function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            // Clear TestBench's Blade view cache before any test suite run.
            if (! static::$viewCacheCleared) {
                Artisan::call('view:clear');

                static::$viewCacheCleared = true;
            }
        });

        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('view.paths', [__DIR__.'/views']);
        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');
    }
}
