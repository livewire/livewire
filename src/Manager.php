<?php

namespace Livewire;

use Livewire\Features\SupportUnitTesting\Testable;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Mechanisms\RenderComponent;

use function Synthetic\trigger;

class Manager
{
    function component($name, $class = null)
    {
        app(ComponentRegistry::class)->register($name, $class);
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

    function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $driver = null)
    {
         Testable::actingAs($user, $driver);

         return $this;
    }

    function isLivewireRequest()
    {
        return request()->hasHeader('X-Livewire');
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
