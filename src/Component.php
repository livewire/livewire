<?php

namespace Livewire;

use Illuminate\View\View;
use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use Livewire\ImplicitlyBoundMethod;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Container\Container;
use Livewire\Exceptions\PropertyNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Exceptions\CannotUseReservedLivewireComponentProperties;

abstract class Component
{
    use Macroable { __call as macroCall; }

    use ComponentConcerns\ValidatesInput,
        ComponentConcerns\HandlesActions,
        ComponentConcerns\ReceivesEvents,
        ComponentConcerns\PerformsRedirects,
        ComponentConcerns\TracksRenderedChildren,
        ComponentConcerns\InteractsWithProperties;

    public $id;

    protected $queryString = [];
    protected $computedPropertyCache = [];
    protected $shouldSkipRender = null;
    protected $preRenderedView;
    protected $forStack = [];
    
    public function __construct($id = null)
    {
        $this->id = $id ?? str()->random(20);

        $this->ensureIdPropertyIsntOverridden();

        Livewire::setBackButtonCache();
    }

    public function __invoke(Container $container, Route $route)
    {
        // With octane and full page components the route is caching the
        // component, so always create a fresh instance.
        $instance = new static;

        // For some reason Octane doesn't play nice with the injected $route.
        // We need to override it here. However, we can't remove the actual
        // param from the method signature as it would break inheritance.
        $route = request()->route() ?? $route;

        try {
            $componentParams = (new ImplicitRouteBinding($container))
                ->resolveAllParameters($route, $instance);
        } catch (ModelNotFoundException $exception) {
            if (method_exists($route,'getMissing') && $route->getMissing()) {
                return $route->getMissing()(request());
            }

            throw $exception;
        }

        $manager = LifecycleManager::fromInitialInstance($instance)
            ->boot()
            ->initialHydrate()
            ->mount($componentParams)
            ->renderToView();

        if ($instance->redirectTo) {
            return redirect()->response($instance->redirectTo);
        }

        $instance->ensureViewHasValidLivewireLayout($instance->preRenderedView);

        $layout = $instance->preRenderedView->livewireLayout;

        return app('view')->file(__DIR__."/Macros/livewire-view-{$layout['type']}.blade.php", [
            'view' => $layout['view'],
            'params' => $layout['params'],
            'slotOrSection' => $layout['slotOrSection'],
            'manager' => $manager,
        ]);
    }

    protected function ensureIdPropertyIsntOverridden()
    {
        throw_if(
            array_key_exists('id', $this->getPublicPropertiesDefinedBySubClass()),
            new CannotUseReservedLivewireComponentProperties('id', $this::getName())
        );
    }

    public function bootIfNotBooted()
    {
        if (method_exists($this, $method = 'boot')) {
            ImplicitlyBoundMethod::call(app(), [$this, $method]); 
        }
    }

    public function initializeTraits()
    {
        foreach (class_uses_recursive($class = static::class) as $trait) {
            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                $this->{$method}();
            }
        }
    }

    public static function getName()
    {
        $namespace = collect(explode('.', str_replace(['/', '\\'], '.', config('livewire.class_namespace'))))
            ->map([Str::class, 'kebab'])
            ->implode('.');

        $fullName = collect(explode('.', str_replace(['/', '\\'], '.', static::class)))
            ->map([Str::class, 'kebab'])
            ->implode('.');

        if (str($fullName)->startsWith($namespace)) {
            return (string) str($fullName)->substr(strlen($namespace) + 1);
        }

        return $fullName;
    }

    public function getQueryString()
    {
        $componentQueryString = method_exists($this, 'queryString')
            ? $this->queryString()
            : $this->queryString;

        return collect(class_uses_recursive($class = static::class))
            ->map(function ($trait) use ($class) {
                $member = 'queryString' . class_basename($trait);

                if (method_exists($class, $member)) {
                    return $this->{$member}();
                }

                if (property_exists($class, $member)) {
                    return $this->{$member};
                }

                return [];
            })
            ->values()
            ->mapWithKeys(function ($value) {
                return $value;
            })
            ->merge($componentQueryString)
            ->toArray();
    }

    public function skipRender()
    {
        $this->shouldSkipRender = true;
    }

    public function renderToView()
    {
        if ($this->shouldSkipRender) return null;

        Livewire::dispatch('component.rendering', $this);

        $view = method_exists($this, 'render')
            ? app()->call([$this, 'render'])
            : view("livewire.{$this::getName()}");

        if (is_string($view)) {
            $view = app('view')->make(CreateBladeView::fromString($view));
        }

        throw_unless($view instanceof View,
            new \Exception('"render" method on ['.get_class($this).'] must return instance of ['.View::class.']'));

        Livewire::dispatch('component.rendered', $this, $view);

        return $this->preRenderedView = $view;
    }

    protected function ensureViewHasValidLivewireLayout(View $view)
    {
        $layout = $view->livewireLayout ?? [];

        $isValid = isset($layout['view'], $layout['type'], $layout['params'], $layout['slotOrSection']);

        if (!$isValid) {
            $view->layout($layout['view'] ?? config('livewire.layout'), $layout['params'] ?? []);
            $view->slot($layout['slotOrSection'] ?? $view->livewireLayout['slotOrSection']);
        }
    }

    public function output($errors = null)
    {
        if ($this->shouldSkipRender) return null;

        $view = $this->preRenderedView;

        // In the service provider, we hijack Laravel's Blade engine
        // with our own. However, we only want Livewire hijackings,
        // while we're rendering Livewire components. So we'll
        // activate it here, and deactivate it at the end
        // of this method.
        $engine = app('view.engine.resolver')->resolve('blade');
        $engine->startLivewireRendering($this);

        $this->setErrorBag(
            $errorBag = $errors ?: ($view->getData()['errors'] ?? $this->getErrorBag())
        );

        $previouslySharedErrors = app('view')->getShared()['errors'] ?? new ViewErrorBag;
        $previouslySharedInstance = app('view')->getShared()['_instance'] ?? null;

        $errors = (new ViewErrorBag)->put('default', $errorBag);

        $errors->getBag('default')->merge(
            $previouslySharedErrors->getBag('default')
        );

        $view->with([
            'errors' => $errors,
            '_instance' => $this,
        ] + $this->getPublicPropertiesDefinedBySubClass());

        app('view')->share('errors', $errors);
        app('view')->share('_instance', $this);

        $output = $view->render();

        app('view')->share('errors', $previouslySharedErrors);
        app('view')->share('_instance', $previouslySharedInstance);

        Livewire::dispatch('view:render', $view);

        $engine->endLivewireRendering();

        return $output;
    }

    public function forgetComputed($key = null)
    {
        if (is_null($key)) {
           $this->computedPropertyCache = [];
           return;
        }

        $keys = is_array($key) ? $key : func_get_args();

        collect($keys)->each(function ($i) {
            if (isset($this->computedPropertyCache[$i])) {
                unset($this->computedPropertyCache[$i]);
            }
        });
    }

    public function addToStack($stack, $type, $contents, $key = null)
    {
        $this->forStack[] = [
            'key' => $key ?: $this->id,
            'stack' => $stack,
            'type' => $type,
            'contents' => $contents,
        ];
    }

    public function getForStack()
    {
        return $this->forStack;
    }
    
    function __isset($property)
    {
        try {
            $this->__get($property);
            return true;
        } catch(PropertyNotFoundException $ex) {
            return false;
        }
        return false;
    }

    public function __get($property)
    {
        $studlyProperty = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $property)));

        if (method_exists($this, $computedMethodName = 'get'.$studlyProperty.'Property')) {
            if (isset($this->computedPropertyCache[$property])) {
                return $this->computedPropertyCache[$property];
            }

            return $this->computedPropertyCache[$property] = app()->call([$this, $computedMethodName]);
        }

        throw new PropertyNotFoundException($property, static::getName());
    }

    public function __call($method, $params)
    {
        if (
            in_array($method, ['mount', 'hydrate', 'dehydrate', 'updating', 'updated'])
            || str($method)->startsWith(['updating', 'updated', 'hydrate', 'dehydrate'])
        ) {
            // Eat calls to the lifecycle hooks if the dev didn't define them.
            return;
        }

        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
