<?php

namespace Livewire\Features\SupportUnitTesting;

use function Livewire\invade;
use function Livewire\store;
use function Livewire\on;
use Tests\TestCase;
use Synthetic\TestableSynthetic;
use PHPUnit\Framework\Assert as PHPUnit;
use Livewire\Mechanisms\DataStore;
use Livewire\Features\SupportValidation\TestsValidation;
use Livewire\Features\SupportRedirects\TestsRedirects;
use Livewire\Features\SupportFileDownloads\TestsFileDownloads;
use Livewire\Features\SupportEvents\TestsEvents;
use Illuminate\Support\Traits\Macroable;

use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Arr;

class DuskTestable
{
    static function provide() {
        Route::get('livewire-dusk/{component}', function ($component) {
            $class = urldecode($component);

            return app()->call(app('livewire')->new($class));
        })->middleware('web');

        on('testCase.setUp', function ($testCase) {
            return function () use ($testCase) {
                invade($testCase)->tweakApplication(function () {
                    config()->set('app.debug', true);

                    static::loadTestComponents();
                });
            };
        });

        on('testCase.tearDown', function () {
            static::wipeRuntimeComponentRegistration();
        });

        if (isset($_SERVER['CI'])) {
            \Orchestra\Testbench\Dusk\Options::withoutUI();
        }

        \Laravel\Dusk\Browser::mixin(new \Tests\DuskBrowserMacros);
    }

    static function create($name, $params = [], $queryParams = [])
    {
        if (config('something')) {
            throw new class ($name) extends \Exception {
                public $component;
                public $isDuskShortcircuit = true;
                public function __construct($component) {
                    $this->component = $component;
                }
            };
        }

        if (is_object($name)) {
            $name = $name::class;
        }

        [$class, $method] = static::findTestClassAndMethodThatCalledThis();

        static::registerComponentsForNextTest([
            'foo' => [$class, $method],
        ]);

        $testCase = invade(app()->make('current.test-case'));

        $browser = $testCase->newBrowser($testCase->createWebDriver());

        return $browser->visit('/livewire-dusk/foo')->waitForLivewireToLoad();
    }

    static function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $driver = null)
    {
        //
    }

    static function findTestClassAndMethodThatCalledThis()
    {
        $traces = debug_backtrace(options: DEBUG_BACKTRACE_IGNORE_ARGS, limit: 10);

        foreach ($traces as $trace) {
            if (is_subclass_of($trace['class'], TestCase::class)) {
                return [$trace['class'], $trace['function']];
            }
        }

        throw new \Exception;
    }

    static function loadTestComponents()
    {
        $tmp = __DIR__ . '/_runtime_components.json';

        if (file_exists($tmp)) {
            // We can't just "require" this file because of race conditions...
            $components = json_decode(file_get_contents($tmp));

            foreach ($components as $name => $class) {
               if (is_numeric($name)) {
                    app('livewire')->component($class);
                } elseif (is_array($class)) {
                    [$testClass, $method] = $class;

                    if (! method_exists($testClass, $method)) return;

                    config()->set('something', true);
                    try { (new $testClass)->$method(); } catch (\Exception $e) {
                        if (! $e->isDuskShortcircuit) throw $e;
                        $class = $e->component;
                    }

                    if (is_object($class)) $class = $class::class;
                    config()->set('something', false);

                    app('livewire')->component($name, $class);
                } else {
                    app('livewire')->component($name, $class);
                }
            }
        }
    }

    static function registerComponentsForNextTest($components)
    {
        $tmp = __DIR__ . '/_runtime_components.json';

        file_put_contents($tmp, json_encode($components, JSON_PRETTY_PRINT));
    }

    static function wipeRuntimeComponentRegistration()
    {
        $tmp = __DIR__ . '/_runtime_components.json';

        file_exists($tmp) && unlink($tmp);
    }
}
