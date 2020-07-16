<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\File;
use Livewire\LivewireServiceProvider;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Assert as PHPUnit;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        // \Orchestra\Testbench\Dusk\Options::withoutUI();

        $this->registerMacros();

        $this->afterApplicationCreated(function () {
            $this->makeACleanSlate();
        });

        $this->beforeApplicationDestroyed(function () {
            $this->makeACleanSlate();
        });

        parent::setUp();

        $this->tweakApplication(function () {
            app('livewire')->component(\Tests\Browser\Loading\Component::class);
            app('livewire')->component(\Tests\Browser\PushState\Component::class);
            app('livewire')->component(\Tests\Browser\PushState\NestedComponent::class);
            app('livewire')->component(\Tests\Browser\InputSelect\Component::class);

            app('session')->put('_token', 'this-is-a-hack-because-something-about-validating-the-csrf-token-is-broken');

            app('config')->set('view.paths', [
                __DIR__.'/views',
                resource_path('views'),
            ]);

            config()->set('app.debug', true);
        });
    }

    protected function tearDown(): void
    {
        $this->removeApplicationTweaks();

        parent::tearDown();
    }

    public function makeACleanSlate()
    {
        Artisan::call('view:clear');

        File::deleteDirectory($this->livewireViewsPath());
        File::deleteDirectory($this->livewireClassesPath());
        File::delete(app()->bootstrapPath('cache/livewire-components.php'));
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('view.paths', [
            __DIR__.'/views',
            resource_path('views'),
        ]);

        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', 'Tests\HttpKernel');
    }

    protected function livewireClassesPath($path = '')
    {
        return app_path('Http/Livewire'.($path ? '/'.$path : ''));
    }

    protected function livewireViewsPath($path = '')
    {
        return resource_path('views').'/livewire'.($path ? '/'.$path : '');
    }

    protected function registerMacros()
    {
        Browser::macro('assertNotVisible', function ($selector) {
            $fullSelector = $this->resolver->format($selector);

            PHPUnit::assertFalse(
                $this->resolver->findOrFail($selector)->isDisplayed(),
                "Element [{$fullSelector}] is visible."
            );

            return $this;
        });

        Browser::macro('waitForLivewire', function () {
            return $this->waitForLivewireRequest()->waitForLivewireResponse();
        });

        Browser::macro('waitForLivewireRequest', function () {
            return $this->waitUsing(2, 25, function () {
                return $this->driver->executeScript('return window.livewire.requestIsOut() === true');
            }, 'Livewire request was never triggered');
        });

        Browser::macro('waitForLivewireResponse', function () {
            return $this->waitUsing(5, 25, function () {
                return $this->driver->executeScript('return window.livewire.requestIsOut() === false');
            }, 'Livewire response was never received');
        });

        Browser::macro('assertScript', function () {
            PHPUnit::assertTrue(
                $this->driver->executeScript('return window.livewire.requestIsOut() === false'),
                'Something this'
            );

            return $this;

        });
    }
}
