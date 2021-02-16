<?php

namespace Livewire;

use Illuminate\View\View;
use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Exceptions\CannotUseReservedLivewireComponentProperties;
use Livewire\Exceptions\PropertyNotFoundException;

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
    protected $initialLayoutConfiguration = [];
    protected $shouldSkipRender = false;
    protected $preRenderedView;

    public function __construct($id = null)
    {
        $this->id = $id ?? str()->random(20);

        $this->ensureIdPropertyIsntOverridden();
    }

    public function __invoke(Container $container, Route $route)
    {
        $componentParams = (new ImplicitRouteBinding($container))
            ->resolveAllParameters($route, $this);

        $manager = LifecycleManager::fromInitialInstance($this)
            ->initialHydrate()
            ->mount($componentParams)
            ->renderToView();

        if ($this->redirectTo) {
            return redirect()->response($this->redirectTo);
        }

        $layoutType = $this->initialLayoutConfiguration['type'] ?? 'component';

        return app('view')->file(__DIR__."/Macros/livewire-view-{$layoutType}.blade.php", [
            'view' => $this->initialLayoutConfiguration['view'] ?? config('livewire.layout'),
            'params' => $this->initialLayoutConfiguration['params'] ?? [],
            'slotOrSection' => $this->initialLayoutConfiguration['slotOrSection'] ?? [
                'extends' => 'content', 'component' => 'slot',
            ][$layoutType],
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
        return $this->queryString;
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

        // Get the layout config from the view.
        if ($view->livewireLayout) {
            $this->initialLayoutConfiguration = $view->livewireLayout;
        }

        Livewire::dispatch('component.rendered', $this, $view);

        return $this->preRenderedView = $view;
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

    public function normalizePublicPropertiesForJavaScript()
    {
        foreach ($this->getPublicPropertiesDefinedBySubClass() as $key => $value) {
            if (is_array($value)) {
                $this->$key = $this->reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
            }

            if ($value instanceof EloquentCollection) {
                // Preserve collection items order by reindexing underlying array.
                $this->$key = $value->values();
            }
        }
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
