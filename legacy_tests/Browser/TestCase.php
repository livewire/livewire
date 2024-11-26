<?php

namespace LegacyTests\Browser;

use Throwable;
use Sushi\Sushi;
use Psy\Shell;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;
use Orchestra\Testbench\Dusk\Options as DuskOptions;
use Livewire\LivewireServiceProvider;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Database\Eloquent\Model;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Exception;
use Closure;
use Livewire\Features\SupportTesting\ShowDuskComponent;

class TestCase extends BaseTestCase
{
    use SupportsSafari;

    public static $useSafari = false;
    public static $useAlpineV3 = false;

    function visitLivewireComponent($browser, $classes, $queryString = '')
    {
        $classes = (array) $classes;

        $this->registerComponentForNextTest($classes);

        $url = '/livewire-dusk/'.urlencode(head($classes)).$queryString;

        return $browser->visit($url)->waitForLivewireToLoad();
    }

    function registerComponentForNextTest($components)
    {
        $tmp = __DIR__ . '/_runtime_components.json';

        file_put_contents($tmp, json_encode($components, JSON_PRETTY_PRINT));
    }

    function wipeRuntimeComponentRegistration()
    {
        $tmp = __DIR__ . '/_runtime_components.json';

        file_exists($tmp) && unlink($tmp);
    }

    public function setUp(): void
    {
        if (isset($_SERVER['CI'])) {
            DuskOptions::withoutUI();
        }

        Browser::mixin(new \Livewire\Features\SupportTesting\DuskBrowserMacros);

        $this->afterApplicationCreated(function () {
            $this->makeACleanSlate();
        });

        $this->beforeApplicationDestroyed(function () {
            $this->makeACleanSlate();
        });

        parent::setUp();

        // $thing = get_class($this);

        $isUsingAlpineV3 = static::$useAlpineV3;

        $this->tweakApplication(function () use ($isUsingAlpineV3) {
            $tmp = __DIR__ . '/_runtime_components.json';
            if (file_exists($tmp)) {
                // We can't just "require" this file because of race conditions...
                $components = json_decode(file_get_contents($tmp));

                foreach ($components as $name => $class) {
                    if (is_numeric($name)) {
                        app('livewire')->component($class);
                    } else {
                        app('livewire')->component($name, $class);
                    }
                }
            }

            // // Autoload all Livewire components in this test suite.
            // collect(File::allFiles(__DIR__))
            //     ->map(function ($file) {
            //         return 'Tests\\Browser\\'.str($file->getRelativePathname())->before('.php')->replace('/', '\\');
            //     })
            //     ->filter(function ($computedClassName) {
            //         return class_exists($computedClassName);
            //     })
            //     ->filter(function ($class) {
            //         return is_subclass_of($class, Component::class);
            //     })->each(function ($componentClass) {
            //         app('livewire')->component($componentClass);
            //     });

            // Route::get(
            //     '/livewire-dusk/tests/browser/sync-history-without-mount/{id}',
            //     \LegacyTests\Browser\SyncHistory\ComponentWithMount::class
            // )->middleware('web')->name('sync-history-without-mount');

            // // This needs to be registered for Dusk to test the route-parameter binding
            // // See: \LegacyTests\Browser\SyncHistory\Test.php
            // Route::get(
            //     '/livewire-dusk/tests/browser/sync-history/{step}',
            //     \LegacyTests\Browser\SyncHistory\Component::class
            // )->middleware('web')->name('sync-history');

            // Route::get(
            //     '/livewire-dusk/tests/browser/sync-history-without-query-string/{step}',
            //     \LegacyTests\Browser\SyncHistory\ComponentWithoutQueryString::class
            // )->middleware('web')->name('sync-history-without-query-string');

            // Route::get(
            //     '/livewire-dusk/tests/browser/sync-history-with-optional-parameter/{step?}',
            //     \LegacyTests\Browser\SyncHistory\ComponentWithOptionalParameter::class
            // )->middleware('web')->name('sync-history-with-optional-parameter');

            // // The following two routes belong together. The first one serves a view which in return
            // // loads and renders a component dynamically. There may not be a POST route for the first one.
            // Route::get('/livewire-dusk/tests/browser/load-dynamic-component', function () {
            //     return View::file(__DIR__ . '/DynamicComponentLoading/view-load-dynamic-component.blade.php');
            // })->middleware('web')->name('load-dynamic-component');

            // Route::post('/livewire-dusk/tests/browser/dynamic-component', function () {
            //     return View::file(__DIR__ . '/DynamicComponentLoading/view-dynamic-component.blade.php');
            // })->middleware('web')->name('dynamic-component');

            Route::get('/livewire-dusk/{component}', ShowDuskComponent::class)->middleware('web');

            Route::middleware('web')->get('/entangle-turbo', function () {
                return view('turbo', [
                    'link' => '/livewire-dusk/' . urlencode(\LegacyTests\Browser\Alpine\Entangle\ToggleEntangledTurbo::class),
                ]);
            })->name('entangle-turbo');

            // app('session')->put('_token', 'this-is-a-hack-because-something-about-validating-the-csrf-token-is-broken');

            // app('config')->set('view.paths', [
            //     __DIR__.'/views',
            //     resource_path('views'),
            // ]);

            config()->set('app.debug', true);
        });
    }

    protected function tearDown(): void
    {
        $this->wipeRuntimeComponentRegistration();

        $this->removeApplicationTweaks();

        parent::tearDown();
    }

    // We don't want to deal with screenshots or console logs.
    protected function storeConsoleLogsFor($browsers) {}
    protected function captureFailuresFor($browsers) {}

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

        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('filesystems.disks.dusk-downloads', [
            'driver' => 'local',
            'root' => __DIR__.'/downloads',
        ]);
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', 'LegacyTests\HttpKernel');
    }

    protected function livewireClassesPath($path = '')
    {
        return app_path('Livewire'.($path ? '/'.$path : ''));
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

        // $options->addArguments([
        //     'auto-open-devtools-for-tabs',
        // ]);

        return static::$useSafari
            ? RemoteWebDriver::create(
                'http://localhost:9515', DesiredCapabilities::safari()
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
            } catch (Exception|Throwable $e) {
                if (DuskOptions::hasUI()) $this->breakIntoATinkerShell($browsers, $e);

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

class User extends AuthUser
{
    use Sushi;

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected $rows = [
        [
            'name' => 'First User',
            'email' => 'first@laravel-livewire.com',
            'password' => '',
        ],
        [
            'name' => 'Second user',
            'email' => 'second@laravel-livewire.com',
            'password' => '',
        ],
    ];
}

class Post extends Model
{
    use Sushi;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $rows = [
        ['title' => 'First', 'user_id' => 1],
        ['title' => 'Second', 'user_id' => 2],
    ];
}

class PostPolicy
{
    public function update(User $user, Post $post)
    {
        return (int) $post->user_id === (int) $user->id;
    }
}
