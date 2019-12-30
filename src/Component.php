<?php

namespace Livewire;

use Illuminate\View\View;
use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Traits\Macroable;
use Livewire\PassPublicPropertiesToView;
use Livewire\Exceptions\PublicPropertyTypeNotAllowedException;

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

    protected $lifecycleHooks = [
        'mount', 'hydrate', 'updating', 'updated',
    ];

    protected $computedPropertyCache = [];

    public function __construct($id)
    {
        $this->id = $id;

        $this->initializeTraits();
    }

    protected function initializeTraits()
    {
        foreach (class_uses_recursive($class = static::class) as $trait) {
            $method = 'initialize'.class_basename($trait);

            if (method_exists($class, $method)) {
                $this->{$method}();
            }
        }
    }

    public function getName()
    {
        $namespace = collect(explode('.', str_replace(['/', '\\'], '.', config('livewire.class_namespace', 'App\\Http\\Livewire'))))
            ->map([Str::class, 'kebab'])
            ->implode('.');

        $name = collect(explode('.', str_replace(['/', '\\'], '.', static::class)))
            ->map([Str::class, 'kebab'])
            ->implode('.');

        if (Str::startsWith($name, $namespace)) {
            return Str::substr($name, strlen($namespace) + 1);
        }

        return $name;
    }

    public function render()
    {
        return view("livewire.{$this->getName()}");
    }

    public function renderWhen()
    {
        return true;
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

        if (!$this->renderWhen()) {
            return '<div></div>';
        }

        $view = $this->render();

        // Normalize all the public properties in the component for JavaScript.
        $this->normalizePublicPropertiesForJavaScript();

        throw_unless($view instanceof View,
            new \Exception('"render" method on ['.get_class($this).'] must return instance of ['.View::class.']'));

        $this->setErrorBag(
            $errorBag = $errors ?: ($view->errors ?: $this->getErrorBag())
        );

        $uses = array_flip(class_uses_recursive(static::class));
        $shouldPassPublicPropertiesToView = isset($uses[PassPublicPropertiesToView::class]);

        $view->with([
            'errors' => (new ViewErrorBag)->put('default', $errorBag),
            '_instance' => $this,
        ] + ($shouldPassPublicPropertiesToView ? $this->getPublicPropertiesDefinedBySubClass() : []));

        $output = $view->render();

        $engine->endLivewireRendering();

        return $output;
    }

    public function normalizePublicPropertiesForJavaScript()
    {
        $normalizedProperties = $this->castDataToJavaScriptReadableTypes(
            $this->reindexArraysWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder(
                $this->castDataFromUserDefinedCasters(
                    $this->getPublicPropertiesDefinedBySubClass()
                )
            )
        );

        foreach ($normalizedProperties as $key => $value) {
            $this->setPropertyValue($key, $value);
        }
    }

    public function castDataFromUserDefinedCasters($data)
    {
        $dataCaster = new DataCaster;
        $casts = $this->casts;

        return collect($data)->map(function ($value, $key) use ($casts, $dataCaster) {
            if (isset($casts[$key])) {
                $type = $casts[$key];

                return $dataCaster->castFrom($type, $value);
            } else {
                return $value;
            }
        })->all();
    }

    protected function reindexArraysWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($data)
    {
        if (! is_array($data)) {
            return $data;
        }

        $normalizedData = $data;

        // Make sure string keys are last (but not ordered). JSON.parse will do this.
        uksort($normalizedData, function ($a, $b) {
            return is_string($a) && is_numeric($b)
                ? 1
                : 0;
        });

        // Order numeric indexes.
        uksort($normalizedData, function ($a, $b) {
            return is_numeric($a) && is_numeric($b)
                ? $a > $b
                : 0;
        });

        return array_map(function ($value) {
            return $this->reindexArraysWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
        }, $normalizedData);
    }

    public function castDataToJavaScriptReadableTypes($data)
    {
        array_walk($data, function ($value, $key) {
            throw_unless(
                is_bool($value) || is_null($value) || is_array($value) || is_numeric($value) || is_string($value),
                new PublicPropertyTypeNotAllowedException($this->getName(), $key, $value)
            );
        });

        return json_decode(json_encode($data), true);
    }

    public function __call($method, $params)
    {
        if (
            in_array($method, $this->lifecycleHooks)
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

    public function __get($property)
    {
        if (method_exists($this, $computedMethodName = 'get'.ucfirst($property).'Property')) {
            if (isset($this->computedPropertyCache[$property])) {
                return $this->computedPropertyCache[$property];
            } else {
                return $this->computedPropertyCache[$property] = $this->$computedMethodName();
            }
        }

        throw new \Exception("Property [{$property}] does not exist on the Component instance.");
    }
}
