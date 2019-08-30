<?php

namespace Livewire;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;
use Livewire\ComponentSessionManager;
use Livewire\Exceptions\CannotAddEloquentModelsAsPublicPropertyException;

abstract class Component
{
    use Concerns\ValidatesInput,
        Concerns\DetectsDirtyProperties,
        Concerns\HandlesActions,
        Concerns\PerformsRedirects,
        Concerns\ReceivesEvents,
        Concerns\InteractsWithProperties,
        Concerns\TracksRenderedChildren;

    public $id;
    protected $lifecycleHooks = [
        'mount', 'hydrate', 'updating', 'updated',
    ];

    public function __construct($id)
    {
        $this->id = $id;
        $this->initializeTraits();
    }

    protected function initializeTraits()
    {
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                $this->{$method}();
            }
        }
    }

    public function getName()
    {
        return collect(explode('.', str_replace(['/', '\\'], '.', static::class)))
            ->diff(['App', 'Http', 'Livewire'])
            ->map([Str::class, 'kebab'])
            ->implode('.');
    }

    public function render()
    {
        return view("livewire.{$this->getName()}");
    }

    public function session($key = null, $value = null)
    {
        $sessionManager = new ComponentSessionManager($this);

        if (is_null($key)) {
            return $sessionManager;
        }

        if (is_null($value)) {
            return $sessionManager->get($key);
        }

        return $sessionManager->put($key, $value);
    }

    public function output($errors = null)
    {
        $view = $this->render();

        throw_unless($view instanceof View,
            new \Exception('"render" method on ['.get_class($this).'] must return instance of ['.View::class.']'));

        return $view
            ->with([
                'errors' => (new ViewErrorBag)->put('default', $errors ?: new MessageBag),
                '_instance' => $this,
            ])
            // Automatically inject all public properties into the blade view.
            ->with($this->getPublicDataFromComponent())
            ->render();
    }

    protected function getPublicDataFromComponent()
    {
        $data = $this->getPublicPropertiesDefinedBySubClass();

        $this->makeSureThereAreNoEloquentModels($data);

        return $this->castDataToJavaScriptSafeTypes($data);
    }

    public function makeSureThereAreNoEloquentModels($data)
    {
        array_walk($data, function ($value) {
            throw_if($value instanceof Model || $value instanceof Collection,
                new CannotAddEloquentModelsAsPublicPropertyException);
        });
    }

    public function castDataToJavaScriptSafeTypes($data)
    {
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

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
