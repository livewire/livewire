<?php

namespace Livewire;

use Livewire\Exceptions\ComponentNotFoundException;
use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Testing\TestableLivewire;

class Manager
{
    protected $listeners = [];
    protected $componentAliases = [];
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

    public function component($alias, $viewClass = null)
    {
        if (is_null($viewClass)) {
            $viewClass = $alias;
            $alias = $viewClass::getName();
        }

        $this->componentAliases[$alias] = $viewClass;
    }

    public function getAlias($class, $default = null)
    {
        $alias = array_search($class, $this->componentAliases);

        return $alias === false ? $default : $alias;
    }

    public function getComponentAliases()
    {
        return $this->componentAliases;
    }

    public function getClass($alias)
    {
        $finder = app(LivewireComponentsFinder::class);

        $class = false;

        $class = $class ?: (
            // Let's first check if the user registered the component using:
            // Livewire::component('name', [Livewire component class]);
            // If not, we'll look in the auto-discovery manifest.
            $this->componentAliases[$alias] ?? $finder->find($alias)
        );

        $class = $class ?: (
            // If none of the above worked, our last-ditch effort will be
            // to re-generate the auto-discovery manifest and look again.
            $finder->build()->find($alias)
        );

        throw_unless($class, new ComponentNotFoundException(
            "Unable to find component: [{$alias}]"
        ));

        return $class;
    }

    public function getInstance($component, $id)
    {
        $componentClass = $this->getClass($component);

        throw_unless(class_exists($componentClass), new ComponentNotFoundException(
            "Component [{$component}] class not found: [{$componentClass}]"
        ));

        return new $componentClass($id);
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

    public function flushState()
    {
        static::$isLivewireRequestTestingOverride = false;
        static::$currentCompilingChildCounter = null;
        static::$currentCompilingViewPath = null;

        $this->shouldDisableBackButtonCache = false;

        $this->dispatch('flush-state');
    }
}
