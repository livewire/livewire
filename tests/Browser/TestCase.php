<?php

namespace Tests\Browser;

use Closure;
use Exception;
use Psy\Shell;
use Throwable;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Livewire\LivewireServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Livewire\Macros\DuskBrowserMacros;
use Orchestra\Testbench\Dusk\Options as DuskOptions;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use SupportsSafari;

    public static $useSafari = false;

    public function setUp(): void
    {
        // DuskOptions::withoutUI();
        if (isset($_SERVER['CI'])) {
            DuskOptions::withoutUI();
        }

        Browser::mixin(new DuskBrowserMacros);

        $this->afterApplicationCreated(function () {
            $this->makeACleanSlate();
        });

        $this->beforeApplicationDestroyed(function () {
            $this->makeACleanSlate();
        });

        parent::setUp();

        $this->tweakApplication(function () {
            app('livewire')->component(\Tests\Browser\Loading\Component::class);
            app('livewire')->component(\Tests\Browser\QueryString\Component::class);
            app('livewire')->component(\Tests\Browser\QueryString\NestedComponent::class);
            app('livewire')->component(\Tests\Browser\QueryString\DirtyDataComponent::class);
            app('livewire')->component(\Tests\Browser\QueryString\HugeComponent::class);
            app('livewire')->component(\Tests\Browser\DataBinding\InputSelect\Component::class);
            app('livewire')->component(\Tests\Browser\FileDownloads\Component::class);
            app('livewire')->component(\Tests\Browser\Redirects\Component::class);
            app('livewire')->component(\Tests\Browser\SupportCollections\Component::class);
            app('livewire')->component(\Tests\Browser\SupportStringables\Component::class);
            app('livewire')->component(\Tests\Browser\Events\Component::class);
            app('livewire')->component(\Tests\Browser\Events\NestedComponentA::class);
            app('livewire')->component(\Tests\Browser\Events\NestedComponentB::class);
            app('livewire')->component(\Tests\Browser\Prefetch\Component::class);
            app('livewire')->component(\Tests\Browser\SupportDateTimes\Component::class);
            app('livewire')->component(\Tests\Browser\DataBinding\DirtyDetection\Component::class);
            app('livewire')->component(\Tests\Browser\DataBinding\AutoFill\Component::class);
            app('livewire')->component(\Tests\Browser\DataBinding\InputText\Component::class);
            app('livewire')->component(\Tests\Browser\DataBinding\InputTextarea\Component::class);
            app('livewire')->component(\Tests\Browser\DataBinding\InputCheckboxRadio\Component::class);
            app('livewire')->component(\Tests\Browser\Actions\Component::class);
            app('livewire')->component(\Tests\Browser\Init\Component::class);
            app('livewire')->component(\Tests\Browser\Dirty\Component::class);
            app('livewire')->component(\Tests\Browser\Alpine\Component::class);
            app('livewire')->component(\Tests\Browser\Alpine\SmallComponent::class);
            app('livewire')->component(\Tests\Browser\Alpine\Entangle\Component::class);
            app('livewire')->component(\Tests\Browser\Alpine\Transition\DollarSignWireComponent::class);
            app('livewire')->component(\Tests\Browser\Alpine\Transition\EntangleComponent::class);
            app('livewire')->component(\Tests\Browser\Alpine\Transition\EntangleDeferComponent::class);
            app('livewire')->component(\Tests\Browser\Alpine\EntangleArray\Component::class);
            app('livewire')->component(\Tests\Browser\Hooks\Component::class);
            app('livewire')->component(\Tests\Browser\Ignore\Component::class);
            app('livewire')->component(\Tests\Browser\Morphdom\Component::class);
            app('livewire')->component(\Tests\Browser\ScriptTag\Component::class);
            app('livewire')->component(\Tests\Browser\Polling\Component::class);
            app('livewire')->component(\Tests\Browser\GlobalLivewire\Component::class);
            app('livewire')->component(\Tests\Browser\Nesting\Component::class);
            app('livewire')->component(\Tests\Browser\Nesting\NestedComponent::class);
            app('livewire')->component(\Tests\Browser\Extensions\Component::class);
            app('livewire')->component(\Tests\Browser\Defer\Component::class);
            app('livewire')->component(\Tests\Browser\SyncHistory\Component::class);
            app('livewire')->component(\Tests\Browser\SyncHistory\ChildComponent::class);
            app('livewire')->component(\Tests\Browser\SyncHistory\SingleRadioComponent::class);
            app('livewire')->component(\Tests\Browser\SyncHistory\ComponentWithMount::class);
            app('livewire')->component(\Tests\Browser\SyncHistory\ComponentWithoutQueryString::class);
            app('livewire')->component(\Tests\Browser\SyncHistory\ComponentWithAlpineEntangle::class);
            app('livewire')->component(\Tests\Browser\Pagination\Tailwind::class);
            app('livewire')->component(\Tests\Browser\Pagination\Bootstrap::class);

            Route::get(
                '/livewire-dusk/tests/browser/sync-history-without-mount/{id}',
                \Tests\Browser\SyncHistory\ComponentWithMount::class
            )->middleware('web')->name('sync-history-without-mount');

            // This needs to be registered for Dusk to test the route-parameter binding
            // See: \Tests\Browser\SyncHistory\Test.php
            Route::get(
                '/livewire-dusk/tests/browser/sync-history/{step}',
                \Tests\Browser\SyncHistory\Component::class
            )->middleware('web')->name('sync-history');

            Route::get(
                '/livewire-dusk/tests/browser/sync-history-without-query-string/{step}',
                \Tests\Browser\SyncHistory\ComponentWithoutQueryString::class
            )->middleware('web')->name('sync-history-without-query-string');

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

    // We don't want to deal with screenshots or console logs.
    protected function storeConsoleLogsFor($browsers)
    {
    }
    protected function captureFailuresFor($browsers)
    {
    }

    public function makeACleanSlate()
    {
        Artisan::call('view:clear');

        File::deleteDirectory($this->livewireViewsPath());
        File::cleanDirectory(__DIR__.'/downloads');
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

        $app['config']->set('filesystems.disks.dusk-downloads', [
            'driver' => 'local',
            'root' => __DIR__.'/downloads',
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

    protected function driver(): RemoteWebDriver
    {
        $options = DuskOptions::getChromeOptions();

        $options->setExperimentalOption('prefs', [
            'download.default_directory' => __DIR__.'/downloads',
        ]);

        return static::$useSafari
            ? RemoteWebDriver::create(
                'http://localhost:9515',
                DesiredCapabilities::safari()
            )
            : RemoteWebDriver::create(
                'http://localhost:9515',
                DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY,
                    $options
                )
            );
    }

    public function browse(Closure $callback)
    {
        parent::browse(function (...$browsers) use ($callback) {
            try {
                $callback(...$browsers);
            } catch (Exception $e) {
                if (DuskOptions::hasUI()) {
                    $this->breakIntoATinkerShell($browsers, $e);
                }

                throw $e;
            } catch (Throwable $e) {
                if (DuskOptions::hasUI()) {
                    $this->breakIntoATinkerShell($browsers, $e);
                }

                throw $e;
            }
        });
    }

    public function breakIntoATinkerShell($browsers, $e)
    {
        $sh = new Shell();

        $sh->add(new DuskCommand($this, $e));

        $sh->setScopeVariables([
            'browsers' => $browsers,
        ]);

        $sh->addInput('dusk');

        $sh->setBoundObject($this);

        $sh->run();

        return $sh->getScopeVariables(false);
    }
}
