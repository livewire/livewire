<?php

namespace Livewire;

use function Livewire\trigger;
use Orchestra\DuskUpdater\UpdateCommand;
use Livewire\Mechanisms\UpdateComponents\UpdateComponents;
use Livewire\Mechanisms\RenderComponent;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Features\SupportUnitTesting\Testable;
use Livewire\Features\SupportUnitTesting\DuskTestable;

class Manager
{
    function component($name, $class = null)
    {
        app(ComponentRegistry::class)->register($name, $class);
    }

    function componentHook($hook)
    {
        app(ComponentRegistry::class)->componentHook($hook);
    }

    function synth($synthClass)
    {
        app(UpdateComponents::class)->registerSynth($synthClass);
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
        return app(RenderComponent::class)->mount($name, $params, $key);
    }

    function snapshot($component, $initial = false)
    {
        $effects = [];

        return app(UpdateComponents::class)->toSnapshot($component, $effects, $initial);
    }

    function update($snapshot, $diff, $calls)
    {
        return app(UpdateComponents::class)->update($snapshot, $diff, $calls);
    }

    function updateProperty($component, $path, $value, $skipHydrate = false)
    {
        return app(UpdateComponents::class)->updateProperty($component, $path, $value, $skipHydrate);
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
