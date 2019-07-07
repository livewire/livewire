<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public static $hasRunLivewireTestingSetup = false;

    public function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            if (! static::$hasRunLivewireTestingSetup) {
                $this->makeACleanSlate();

                static::$hasRunLivewireTestingSetup = true;
            }
        });

        parent::setUp();
    }

    public function makeACleanSlate()
    {
        Artisan::call('view:clear');

        File::deleteDirectory($this->livewireViewsPath());
        File::deleteDirectory($this->livewireClassesPath());
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

    protected function livewireClassesPath($path = '')
    {
        return app_path('Http/Livewire' . ($path ? '/'.$path : ''));
    }

    protected function livewireViewsPath($path = '')
    {
        return config('view.paths')[0].'/livewire' . ($path ? '/'.$path : '');
    }
}
