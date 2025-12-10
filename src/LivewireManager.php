<?php

namespace Livewire;

use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Features\SupportTesting\DuskTestable;
use Livewire\Features\SupportLazyLoading\SupportLazyLoading;
use Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets;

class LivewireManager
{
    protected LivewireServiceProvider $provider;

    public static $v4 = true;

    function setProvider(LivewireServiceProvider $provider)
    {
        $this->provider = $provider;
    }

    function provide($callback)
    {
        \Closure::bind($callback, $this->provider, $this->provider::class)();
    }

    function component($name, $class = null)
    {
        $this->addComponent($name, class: $class);
    }

    function addComponent($name, $viewPath = null, $class = null)
    {
        app('livewire.finder')->addComponent($name, class: $class, viewPath: $viewPath);
    }

    function addLocation($viewPath = null, $classNamespace = null)
    {
        return app('livewire.finder')->addLocation(classNamespace: $classNamespace, viewPath: $viewPath);
    }

    function addNamespace($namespace, $viewPath = null, $classNamespace = null, $classPath = null, $classViewPath = null)
    {
        return app('livewire.finder')->addNamespace($namespace, classNamespace: $classNamespace, viewPath: $viewPath, classPath: $classPath, classViewPath: $classViewPath);
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

    function precompiler($callback)
    {
        app(ExtendBlade::class)->livewireOnlyPrecompiler($callback);
    }

    function prepareViewsForCompilationUsing(callable $callback)
    {
        app('livewire.compiler')->prepareViewsForCompilationUsing($callback);
    }

    function new($name, $id = null)
    {
        return app('livewire.factory')->create($name, $id);
    }

    /**
     * @deprecated This method will be removed in a future version. Use exists() instead.
     */
    function isDiscoverable($componentNameOrClass)
    {
        return $this->exists($componentNameOrClass);
    }

    function exists($componentNameOrClass)
    {
        return app('livewire.factory')->exists($componentNameOrClass);
    }

    function resolveMissingComponent($resolver)
    {
        return app('livewire.factory')->resolveMissingComponent($resolver);
    }

    function mount($name, $params = [], $key = null, $slots = [])
    {
        return app(HandleComponents::class)->mount($name, $params, $key, $slots);
    }

    function snapshot($component, $context = null)
    {
        return app(HandleComponents::class)->snapshot($component, $context);
    }

    function fromSnapshot($snapshot)
    {
        return app(HandleComponents::class)->fromSnapshot($snapshot);
    }

    function listen($eventName, $callback) {
        return on($eventName, $callback);
    }

    function current()
    {
        return last(app(HandleComponents::class)::$componentStack);
    }

    function findSynth($keyOrTarget, $component)
    {
        return app(HandleComponents::class)->findSynth($keyOrTarget, $component);
    }

    function update($snapshot, $diff, $calls)
    {
        return app(HandleComponents::class)->update($snapshot, $diff, $calls);
    }

    function updateProperty($component, $path, $value)
    {
        $dummyContext = new ComponentContext($component, false);

        $updatedHook = app(HandleComponents::class)->updateProperty($component, $path, $value, $dummyContext);

        $updatedHook();
    }

    function isLivewireRequest()
    {
        return app(HandleRequests::class)->isLivewireRequest();
    }

    function componentHasBeenRendered()
    {
        return SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest;
    }

    function forceAssetInjection()
    {
        SupportAutoInjectedAssets::$forceAssetInjection = true;
    }

    function setUpdateRoute($callback)
    {
        return app(HandleRequests::class)->setUpdateRoute($callback);
    }

    function getUriPrefix()
    {
        return app(HandleRequests::class)->getUriPrefix();
    }

    function getUpdateUri()
    {
        return app(HandleRequests::class)->getUpdateUri();
    }

    function setScriptRoute($callback)
    {
        return app(FrontendAssets::class)->setScriptRoute($callback);
    }

    function useScriptTagAttributes($attributes)
    {
        return app(FrontendAssets::class)->useScriptTagAttributes($attributes);
    }

    protected $queryParamsForTesting = [];

    protected $cookiesForTesting = [];

    protected $headersForTesting = [];

    function withUrlParams($params)
    {
        return $this->withQueryParams($params);
    }

    function withQueryParams($params)
    {
        $this->queryParamsForTesting = $params;

        return $this;
    }

    function withCookie($name, $value)
    {
        $this->cookiesForTesting[$name] = $value;

        return $this;
    }

    function withCookies($cookies)
    {
        $this->cookiesForTesting = array_merge($this->cookiesForTesting, $cookies);

        return $this;
    }

    function withHeaders($headers)
    {
        $this->headersForTesting = array_merge($this->headersForTesting, $headers);

        return $this;
    }

    function withoutLazyLoading()
    {
        SupportLazyLoading::disableWhileTesting();

        return $this;
    }

    /**
     * @template TComponent of \Livewire\Component
     *
     * @param class-string<TComponent>|TComponent|string|array<array-key, \Livewire\Component> $name
     * @param array $params
     *
     * @return Testable<TComponent>
     */
    function test($name, $params = [])
    {
        return Testable::create(
            $name,
            $params,
            $this->queryParamsForTesting,
            $this->cookiesForTesting,
            $this->headersForTesting,
        );
    }

    function visit($name, $args = [])
    {
        if (class_exists(\Pest\Browser\Api\Livewire::class)) {
            return \Pest\Browser\Api\Livewire::test($name, $args);
        }

        return DuskTestable::create($name, $params = [], $this->queryParamsForTesting);
    }

    function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $driver = null)
    {
         Testable::actingAs($user, $driver);

         return $this;
    }

    function isRunningServerless()
    {
        return in_array($_ENV['SERVER_SOFTWARE'] ?? null, [
            'vapor',
            'bref',
        ]);
    }

    function addPersistentMiddleware($middleware)
    {
        app(PersistentMiddleware::class)->addPersistentMiddleware($middleware);
    }

    function setPersistentMiddleware($middleware)
    {
        app(PersistentMiddleware::class)->setPersistentMiddleware($middleware);
    }

    function getPersistentMiddleware()
    {
        return app(PersistentMiddleware::class)->getPersistentMiddleware();
    }

    function zap()
    {
        return app('livewire.zap');
    }

    function flushState()
    {
        trigger('flush-state');
    }

    function originalUrl()
    {
        if ($this->isLivewireRequest()) {
            return url()->to($this->originalPath());
        }

        return url()->current();
    }

    function originalPath()
    {
        if ($this->isLivewireRequest()) {
            $snapshot = json_decode(request('components.0.snapshot'), true);

            return data_get($snapshot, 'memo.path', 'POST');
        }

        return request()->path();
    }

    function originalMethod()
    {
        if ($this->isLivewireRequest()) {
            $snapshot = json_decode(request('components.0.snapshot'), true);

            return data_get($snapshot, 'memo.method', 'POST');
        }

        return request()->method();
    }

    function isCspSafe()
    {
        return config('livewire.csp_safe', false);
    }
}
