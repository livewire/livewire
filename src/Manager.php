<?php

namespace Livewire;

use function Livewire\trigger;
use Orchestra\DuskUpdater\UpdateCommand;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\RenderComponent;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Features\SupportUnitTesting\Testable;
use Livewire\Features\SupportUnitTesting\DuskTestable;
use Livewire\ComponentHookRegistry;
use Livewire\ComponentHook;

class Manager
{
    protected ServiceProvider $provider;

    function setProvider(ServiceProvider $provider)
    {
        $this->provider = $provider;
    }

    function provide($callback)
    {
        \Closure::bind($callback, $this->provider, $this->provider::class)();
    }

    function component($name, $class = null)
    {
        app(ComponentRegistry::class)->register($name, $class);
    }

    function componentHook($hook)
    {
        ComponentHookRegistry::register($hook);
    }

    function propertySynthesizer($synth)
    {
        app(HandleComponents::class)->registerPropertySynthesizer($synth);
    }

    function directive($name, $callback)
    {
        app(ExtendBlade::class)->livewireOnlyDirective($name, $callback);
    }

    function precompiler($pattern, $callback)
    {
        app(ExtendBlade::class)->livewireOnlyPrecompiler($pattern, $callback);
    }

    function new($name, $params = [], $id = null)
    {
        return app(ComponentRegistry::class)->new($name, $params, $id);
    }

    function mount($name, $params = [], $key = null)
    {
        return app(HandleComponents::class)->mount($name, $params, $key);
    }

    function render($component, $default = null)
    {
        return app(RenderComponent::class)->render($component, $default);
    }

    function snapshot($component)
    {
        return app(HandleComponents::class)->snapshot($component);
    }

    function fromSnapshot($snapshot)
    {
        return app(HandleComponents::class)->fromSnapshot($snapshot);
    }

    function current()
    {
        return last(app(HandleComponents::class)::$renderStack);
    }

    function update($snapshot, $diff, $calls)
    {
        return app(HandleComponents::class)->update($snapshot, $diff, $calls);
    }

    function updateProperty($component, $path, $value)
    {
        $dummyContext = new ComponentContext($component, false);

        return app(HandleComponents::class)->updateProperty($component, $path, $value, $dummyContext);
    }

    function setUpdateRoute($callback)
    {
        return app(HandleRequests::class)->setUpdateRoute($callback);
    }

    function getUpdateUri()
    {
        return app(HandleRequests::class)->getUpdateUri();
    }

    function setJavaScriptRoute($callback)
    {
        return app(FrontendAssets::class)->setJavaScriptRoute($callback);
    }


    protected $queryParamsForTesting = [];

    function withQueryParams($params)
    {
        $this->queryParamsForTesting = $params;

        return $this;
    }

    function test($name, $params = [])
    {
        return Testable::create($name, $params, $this->queryParamsForTesting);
    }

    function visit($name)
    {
        return DuskTestable::create($name, $params = [], $this->queryParamsForTesting);
    }

    function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $driver = null)
    {
         Testable::actingAs($user, $driver);

         return $this;
    }

    function isLivewireRequest()
    {
        return request()->hasHeader('X-Livewire') || request()->hasHeader('X-Synthetic');
    }

    function isRunningServerless()
    {
        return in_array($_ENV['SERVER_SOFTWARE'] ?? null, [
            'vapor',
            'bref',
        ]);
    }

    function flushState()
    {
        trigger('flush-state');
    }

    protected $jsFeatures = [];

    function enableJsFeature($name)
    {
        $this->jsFeatures[] = $name;
    }

    function getJsFeatures()
    {
        return $this->jsFeatures;
    }
}
