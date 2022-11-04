<?php

namespace Livewire;

use Throwable;
use Livewire\Mechanisms\RenderComponent;
use Livewire\Mechanisms\HijackBlade\HijackBlade;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Features\SupportUnitTesting\Testable;
use Livewire\Features\SupportPageComponents\SupportPageComponents;
use Livewire\Exceptions\ComponentNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Auth\Authenticatable;
use Closure;

use function Synthetic\after;
use function Synthetic\before;
use function Synthetic\on;

class Manager
{
    protected $queryParamsForTesting = [];

    protected $persistentMiddleware = [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \Laravel\Jetstream\Http\Middleware\AuthenticateSession::class,
        \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\RedirectIfAuthenticated::class,
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Auth\Middleware\Authorize::class,
        \App\Http\Middleware\Authenticate::class,
    ];

    public static $isLivewireRequestTestingOverride = false;

    public $currentComponent;
    public static $currentCompilingViewPath;
    public static $currentCompilingChildCounter;

    public function component($name, $class = null)
    {
        if (is_null($class)) {
            [$class, $name] = [$name, $name::generateName()];
        }

        ComponentRegistry::getInstance()->register($name, $class);
    }

    public static $jsFeatures = [];

    public function enableJsFeature($name)
    {
        static::$jsFeatures[] = $name;
    }

    public function getJsFeatures()
    {
        return static::$jsFeatures;
    }

    public function new($name)
    {
        if (is_object($name) && $name instanceof Component) return $name;

        $component = ComponentRegistry::getInstance()->get($name);

        $name = $component::generateName();

        $component->setId(str()->random(20));
        $component->setName($name);

        return $component;
    }

    public function current()
    {
        throw_unless($this->currentComponent, new \Exception(
            'No Livewire component is currently being processed.'
        ));

        return $this->currentComponent;
    }

    public function setCurrent($component)
    {
        $this->currentComponent = $component;
    }

    public function directive($name, $callback)
    {
        HijackBlade::getInstance()->livewireOnlyDirective($name, $callback);
    }

    public function precompiler($pattern, $callback)
    {
        HijackBlade::getInstance()->livewireOnlyPrecompiler($pattern, $callback);
    }

    public function mount($name, $params = [], $key = null, $slots = [], $viewScope = [])
    {
        return RenderComponent::mount($name, $params, $key, $slots, $viewScope);
    }

    public function isRenderingPageComponent()
    {
        return SupportPageComponents::isRenderingPageComponent();
    }

    public function isLivewireRequest()
    {
        return $this->isProbablyLivewireRequest();
    }

    public function isDefinitelyLivewireRequest()
    {
        $route = request()->route();

        if (! $route) return false;

        return $route->named('synthetic.update');
    }

    public function isProbablyLivewireRequest()
    {
        if (static::$isLivewireRequestTestingOverride) return true;

        return request()->hasHeader('X-Livewire');
    }

    /**
     * Render a Livewire component's Blade view to raw HTML (without all the dehydration metadata...)
     */
    public function renderBladeView($target, $blade, $data)
    {
        return RenderComponent::getInstance()->renderComponentBladeView($target, $blade, $data);
    }

    function withQueryParams($params)
    {
        $this->queryParamsForTesting = $params;

        return $this;
    }

    public function test($name, $params = [])
    {
        $uri = 'livewire-test';

        $symfonyRequest = \Symfony\Component\HttpFoundation\Request::create(
            $uri, 'GET', $parameters = $this->queryParamsForTesting,
            $cookies = [], $files = [], $server = [], $content = null
        );

        $request = \Illuminate\Http\Request::createFromBase($symfonyRequest);

        app()->instance('request', $request);

        app('request')->headers->set('X-Livewire', true);

        // \Illuminate\Support\Facades\Facade::clearResolvedInstance('request');

        // This allows the user to test a component by it's class name,
        // and not have to register an alias.
        if (class_exists($name)) {
            if (! is_subclass_of($name, Component::class)) {
                throw new \Exception('Class ['.$name.'] is not a subclass of Livewire\Component.');
            }

            $componentClass = $name;

            $this->component($name = str()->random(20), $componentClass);
        }

        $component = null;

        $forget = on('mount', function () use (&$component, &$forget) {
            $forget();

            return function ($instance) use (&$component) {
                $component = $instance;
            };
        });

        [$html, $dehydrated] = $this->mount($name, $params);

        return new Testable($dehydrated, $component);
    }

    public function visit($browser, $class, $queryString = '')
    {
        $url = '/livewire-dusk/'.urlencode($class).$queryString;

        // @todo...
        // return $browser->visit($url)->waitForLivewireToLoad();
        return $browser->visit($url);
    }

    public function actingAs(Authenticatable $user, $driver = null)
    {
        // This is a helper to be used during testing.

        if (isset($user->wasRecentlyCreated) && $user->wasRecentlyCreated) {
            $user->wasRecentlyCreated = false;
        }

        auth()->guard($driver)->setUser($user);

        auth()->shouldUse($driver);

        return $this;
    }

    public function isRunningServerless()
    {
        return in_array($_ENV['SERVER_SOFTWARE'] ?? null, [
            'vapor',
            'bref',
        ]);
    }

    public function flushState()
    {
        static::$isLivewireRequestTestingOverride = false;
        static::$currentCompilingChildCounter = null;
        static::$currentCompilingViewPath = null;

        app('synthetic')->trigger('flush-state');
    }

    function addPersistentMiddleware()
    {
        // @todo
    }
}
