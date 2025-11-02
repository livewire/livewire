<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\Dusk\Options;

class TestCase extends \Orchestra\Testbench\Dusk\TestCase
{
    public function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->makeACleanSlate();

            if (env('DUSK_HEADLESS_DISABLED', true) == false) {
                Options::withoutUI();
            } 
        });

        $this->beforeApplicationDestroyed(function () {
            $this->makeACleanSlate();
        });

        parent::setUp();
    }

    public function makeACleanSlate()
    {
        Artisan::call('view:clear');

        app()->forgetInstance('livewire.factory');

        File::deleteDirectory($this->livewireViewsPath());
        File::deleteDirectory($this->livewireClassesPath());
        File::deleteDirectory($this->livewireComponentsPath());
        File::deleteDirectory($this->livewireTestsPath());
        File::delete(app()->bootstrapPath('cache/livewire-components.php'));
    }

    protected function getPackageProviders($app)
    {
        return [
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('view.paths', [
            __DIR__.'/views',
            resource_path('views'),
        ]);

        // Override layout and page namespaces to use the test views instead of testbench's...
        $app['view']->addNamespace('layouts', __DIR__.'/views/layouts');
        $app['view']->addNamespace('pages', __DIR__.'/views/pages');

        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('filesystems.disks.unit-downloads', [
            'driver' => 'local',
            'root' => __DIR__.'/fixtures',
        ]);
    }

    protected function livewireClassesPath($path = '')
    {
        return app_path('Livewire'.($path ? '/'.$path : ''));
    }

    protected function livewireViewsPath($path = '')
    {
        return resource_path('views').'/livewire'.($path ? '/'.$path : '');
    }

    protected function livewireComponentsPath($path = '')
    {
        return resource_path('views').'/components'.($path ? '/'.$path : '');
    }

    protected function livewireTestsPath($path = '')
    {
        return base_path('tests/Feature/Livewire'.($path ? '/'.$path : ''));
    }

    protected function resolveApplication()
    {
        return parent::resolveApplication()->useEnvironmentPath(__DIR__.'/..');
    }
}
