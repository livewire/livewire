<?php

namespace Livewire;

use Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets;
use Livewire\Features\SupportTesting\DuskTestable;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;

class LivewireManager
{
    protected LivewireServiceProvider $provider;

    public function setProvider(LivewireServiceProvider $provider)
    {
        $this->provider = $provider;
    }

    public function provide($callback)
    {
        \Closure::bind($callback, $this->provider, $this->provider::class)();
    }

    public function component($name, $class = null)
    {
        app(ComponentRegistry::class)->component($name, $class);
    }

    public function componentHook($hook)
    {
        ComponentHookRegistry::register($hook);
    }

    public function propertySynthesizer($synth)
    {
        app(HandleComponents::class)->registerPropertySynthesizer($synth);
    }

    public function directive($name, $callback)
    {
        app(ExtendBlade::class)->livewireOnlyDirective($name, $callback);
    }

    public function precompiler($callback)
    {
        app(ExtendBlade::class)->livewireOnlyPrecompiler($callback);
    }

    public function new($name, $id = null)
    {
        return app(ComponentRegistry::class)->new($name, $id);
    }

    public function isDiscoverable($componentNameOrClass)
    {
        return app(ComponentRegistry::class)->isDiscoverable($componentNameOrClass);
    }

    public function resolveMissingComponent($resolver)
    {
        return app(ComponentRegistry::class)->resolveMissingComponent($resolver);
    }

    public function mount($name, $params = [], $key = null)
    {
        return app(HandleComponents::class)->mount($name, $params, $key);
    }

    public function snapshot($component)
    {
        return app(HandleComponents::class)->snapshot($component);
    }

    public function fromSnapshot($snapshot)
    {
        return app(HandleComponents::class)->fromSnapshot($snapshot);
    }

    public function listen($eventName, $callback)
    {
        return on($eventName, $callback);
    }

    public function current()
    {
        return last(app(HandleComponents::class)::$componentStack);
    }

    public function update($snapshot, $diff, $calls)
    {
        return app(HandleComponents::class)->update($snapshot, $diff, $calls);
    }

    public function updateProperty($component, $path, $value)
    {
        $dummyContext = new ComponentContext($component, false);

        return app(HandleComponents::class)->updateProperty($component, $path, $value, $dummyContext);
    }

    public function isLivewireRequest()
    {
        return app(HandleRequests::class)->isLivewireRequest();
    }

    public function componentHasBeenRendered()
    {
        return SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest;
    }

    public function forceAssetInjection()
    {
        SupportAutoInjectedAssets::$forceAssetInjection = true;
    }

    public function setUpdateRoute($callback)
    {
        return app(HandleRequests::class)->setUpdateRoute($callback);
    }

    public function getUpdateUri()
    {
        return app(HandleRequests::class)->getUpdateUri();
    }

    public function setScriptRoute($callback)
    {
        return app(FrontendAssets::class)->setScriptRoute($callback);
    }

    public function useScriptTagAttributes($attributes)
    {
        return app(FrontendAssets::class)->useScriptTagAttributes($attributes);
    }

    protected $queryParamsForTesting = [];

    protected $cookiesForTesting = [];

    public function withUrlParams($params)
    {
        return $this->withQueryParams($params);
    }

    public function withQueryParams($params)
    {
        $this->queryParamsForTesting = $params;

        return $this;
    }

    public function withCookie($name, $value)
    {
        $this->cookiesForTesting[$name] = $value;

        return $this;
    }

    public function withCookies($cookies)
    {
        $this->cookiesForTesting = array_merge($this->cookiesForTesting, $cookies);

        return $this;
    }

    public function test($name, $params = [])
    {
        return Testable::create($name, $params, $this->queryParamsForTesting, $this->cookiesForTesting);
    }

    public function visit($name)
    {
        return DuskTestable::create($name, $params = [], $this->queryParamsForTesting);
    }

    public function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $driver = null)
    {
        Testable::actingAs($user, $driver);

        return $this;
    }

    public function isRunningServerless()
    {
        return in_array($_ENV['SERVER_SOFTWARE'] ?? null, [
            'vapor',
            'bref',
        ]);
    }

    public function addPersistentMiddleware($middleware)
    {
        app(PersistentMiddleware::class)->addPersistentMiddleware($middleware);
    }

    public function setPersistentMiddleware($middleware)
    {
        app(PersistentMiddleware::class)->setPersistentMiddleware($middleware);
    }

    public function getPersistentMiddleware()
    {
        return app(PersistentMiddleware::class)->getPersistentMiddleware();
    }

    public function flushState()
    {
        trigger('flush-state');
    }

    public function originalUrl()
    {
        if ($this->isLivewireRequest()) {
            return url()->to($this->originalPath());
        }

        return url()->current();
    }

    public function originalPath()
    {
        if ($this->isLivewireRequest()) {
            $snapshot = json_decode(request('components.0.snapshot'), true);

            return data_get($snapshot, 'memo.path', 'POST');
        }

        return request()->path();
    }

    public function originalMethod()
    {
        if ($this->isLivewireRequest()) {
            $snapshot = json_decode(request('components.0.snapshot'), true);

            return data_get($snapshot, 'memo.method', 'POST');
        }

        return request()->method();
    }
}
