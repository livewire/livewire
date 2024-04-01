<?php

namespace Livewire\Features\SupportTesting;

use Illuminate\Support\Facades\Route;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\TestCase;
use function Livewire\{ invade, on };
use Illuminate\Support\Arr;

class DuskTestable
{
    public static $currentTestCase;
    public static $shortCircuitCreateCall = false;
    public static $isTestProcess = false;
    public static $browser;

    static function provide() {
        Route::get('livewire-dusk/{component}', ShowDuskComponent::class)->middleware('web');

        on('browser.testCase.setUp', function ($testCase) {
            static::$currentTestCase = $testCase;
            static::$isTestProcess = true;

            $tweakApplication = $testCase::tweakApplicationHook();

            invade($testCase)->beforeServingApplication(function ($app, $config) use ($tweakApplication) {
                $config->set('app.debug', true);

                if (is_callable($tweakApplication)) $tweakApplication();

                static::loadTestComponents();
            });
        });

        on('browser.testCase.tearDown', function () {
            static::wipeRuntimeComponentRegistration();

            static::$browser && static::$browser->quit();

            static::$currentTestCase = null;
        });

        if (isset($_SERVER['CI']) && class_exists(\Orchestra\Testbench\Dusk\Options::class)) {
            \Orchestra\Testbench\Dusk\Options::withoutUI();
        }

        \Laravel\Dusk\Browser::mixin(new DuskBrowserMacros);
    }

    /**
     * @return Browser
     */
    static function create($components, $params = [], $queryParams = [])
    {
        if (static::$shortCircuitCreateCall) {
            throw new class ($components) extends \Exception {
                public $components;
                public $isDuskShortcircuit = true;
                function __construct($components) {
                    $this->components = $components;
                }
            };
        }

        $components = (array) $components;

        $firstComponent = array_shift($components);

        $id = 'a'.str()->random(10);

        $components = [$id => $firstComponent, ...$components];

        return static::createBrowser($id, $components, $params, $queryParams)->visit('/livewire-dusk/'.$id.'?'.Arr::query($queryParams));
    }

    static function createBrowser($id, $components, $params = [], $queryParams = [])
    {
        if (static::$shortCircuitCreateCall) {
            throw new class ($components) extends \Exception {
                public $components;
                public $isDuskShortcircuit = true;
                function __construct($components) {
                    $this->components = $components;
                }
            };
        }

        [$class, $method] = static::findTestClassAndMethodThatCalledThis();

        static::registerComponentsForNextTest([$id, $class, $method]);

        $testCase = invade(static::$currentTestCase);

        return static::$browser = $testCase->newBrowser($testCase->createWebDriver());
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
        if (static::$isTestProcess) return;

        $tmp = __DIR__ . '/_runtime_components.json';

        if (file_exists($tmp)) {
            // We can't just "require" this file because of race conditions...
            [$id, $testClass, $method] = json_decode(file_get_contents($tmp), associative: true);

            if (! method_exists($testClass, $method)) return;

            static::$shortCircuitCreateCall = true;

            $components = null;

            try {
                if (\Orchestra\Testbench\phpunit_version_compare('10.0', '>=')) {
                    (new $testClass($method))->$method();
                } else {
                    (new $testClass())->$method();
                }
            } catch (\Exception $e) {
                if (! $e->isDuskShortcircuit) throw $e;
                $components = $e->components;
            }

            $components = is_array($components) ? $components : [$components];

            $firstComponent = array_shift($components);

            $components = [$id => $firstComponent, ...$components];

            static::$shortCircuitCreateCall = false;

            foreach ($components as $name => $class) {
                if (is_object($class)) $class = $class::class;

                if (is_numeric($name)) {
                    app('livewire')->component($class);
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

    function breakIntoATinkerShell($browsers, $e)
    {
        $sh = new \Psy\Shell();

        $sh->add(new \Laravel\Dusk\Console\DuskCommand($this, $e));

        $sh->setScopeVariables([
            'browsers' => $browsers,
        ]);

        $sh->addInput('dusk');

        $sh->setBoundObject($this);

        $sh->run();

        return $sh->getScopeVariables(false);
    }
}
