<?php

namespace Livewire;

use Throwable;
use Livewire\Testing\TestableLivewire;
use Livewire\Mechanisms\RenderComponent;
use Livewire\Mechanisms\HijackBlade;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Features\SupportPageComponents\SupportPageComponents;
use Livewire\Exceptions\ComponentNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Auth\Authenticatable;
use Closure;

class Manager
{
    protected $queryParamsForTesting = [];

    protected $shouldDisableBackButtonCache = false;


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

    public static $currentCompilingViewPath;
    public static $currentCompilingChildCounter;

    public function component($name, $class = null)
    {
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
        return ComponentRegistry::getInstance()->get($name);
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

    public function test($name, $params = [])
    {
        return new TestableLivewire($name, $params, $this->queryParamsForTesting);
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

        $this->shouldDisableBackButtonCache = false;

        app('synthetic')->trigger('flush-state');
    }

    function addPersistentMiddleware()
    {
        // @todo
    }
}
