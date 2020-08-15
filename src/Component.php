<?php

namespace Livewire;

use Livewire\Livewire;
use Illuminate\View\View;
use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Exceptions\CannotUseReservedLivewireComponentProperties;

abstract class Component
{
    use Macroable { __call as macroCall; }

    use ComponentConcerns\ValidatesInput,
        ComponentConcerns\HandlesActions,
        ComponentConcerns\ReceivesEvents,
        ComponentConcerns\PerformsRedirects,
        ComponentConcerns\DetectsDirtyProperties,
        ComponentConcerns\TracksRenderedChildren,
        ComponentConcerns\InteractsWithProperties;

    public $id;

    protected $updatesQueryString = [];
    protected $computedPropertyCache = [];

    public function __construct($id)
    {
        $this->id = $id;

        $this->ensureIdPropertyIsntOverridden();

        $this->initializeTraits();
    }

    protected function ensureIdPropertyIsntOverridden()
    {
        throw_if(
            in_array('id', array_keys($this->getPublicPropertiesDefinedBySubClass())),
            new CannotUseReservedLivewireComponentProperties('id', $this->getName())
        );
    }

    protected function initializeTraits()
    {
        foreach (class_uses_recursive($class = static::class) as $trait) {
            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                $this->{$method}();
            }
        }
    }

    public function getName()
    {
        $namespace = collect(explode('.', str_replace(['/', '\\'], '.', config('livewire.class_namespace', 'App\\Http\\Livewire'))))
            ->map([Str::class, 'kebab'])
            ->implode('.');

        $fullName = collect(explode('.', str_replace(['/', '\\'], '.', static::class)))
            ->map([Str::class, 'kebab'])
            ->implode('.');

        if (Str::startsWith($fullName, $namespace)) {
            return Str::substr($fullName, strlen($namespace) + 1);
        }

        return $fullName;
    }

    public function getUpdatesQueryString()
    {
        return $this->updatesQueryString;
    }

    public function getCasts()
    {
        return $this->casts;
    }

    public function render()
    {
        return view("livewire.{$this->getName()}");
    }

    public function output($errors = null)
    {
        // In the service provider, we hijack Laravel's Blade engine
        // with our own. However, we only want Livewire hijackings,
        // while we're rendering Livewire components. So we'll
        // activate it here, and deactivate it at the end
        // of this method.
        $engine = app('view.engine.resolver')->resolve('blade');
        $engine->startLivewireRendering($this);

        $view = $this->render();

        if (is_string($view) && Livewire::isLaravel7()) {
            $view = app('view')->make((new CreateBladeViewFromString)($view));
        }

        $this->normalizePublicPropertiesForJavaScript();

        throw_unless($view instanceof View,
            new \Exception('"render" method on ['.get_class($this).'] must return instance of ['.View::class.']'));

        $this->setErrorBag(
            $errorBag = $errors ?: ($view->getData()['errors'] ?? $this->getErrorBag())
        );

        $previouslySharedErrors = app('view')->getShared()['errors'] ?? new ViewErrorBag;
        $previouslySharedInstance = app('view')->getShared()['_instance'] ?? null;

        $errors = (new ViewErrorBag)->put('default', $errorBag);

        $errors->getBag('default')->merge(
            $previouslySharedErrors->getBag('default')
        );

        app('view')->share('errors', $errors);
        app('view')->share('_instance', $this);

        $view->with([
            'errors' => $errors,
            '_instance' => $this,
        ] + $this->getPublicPropertiesDefinedBySubClass());

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

    protected function reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $normalizedData = $value;

        // Make sure string keys are last (but not ordered) and numeric keys are ordered.
        // JSON.parse will do this on the frontend, so we'll get ahead of it.
        uksort($normalizedData, function ($a, $b) {
            if (is_numeric($a) && is_numeric($b)) return $a > $b;

            if (! is_numeric($a) && ! is_numeric($b)) return 0;

            if (! is_numeric($a)) return 1;
        });

        return array_map(function ($value) {
            return $this->reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
        }, $normalizedData);
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
        if (method_exists($this, $computedMethodName = 'get'.ucfirst($property).'Property')) {
            if (isset($this->computedPropertyCache[$property])) {
                return $this->computedPropertyCache[$property];
            } else {
                return $this->computedPropertyCache[$property] = $this->$computedMethodName();
            }
        }

        throw new \Exception("Property [{$property}] does not exist on the {$this->getName()} component.");
    }

    public function __call($method, $params)
    {
        if (
            in_array($method, ['mount', 'hydrate', 'updating', 'updated'])
            || Str::startsWith($method, ['updating', 'updated'])
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
