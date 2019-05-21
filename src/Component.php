<?php

namespace Livewire;

use BadMethodCallException;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

abstract class Component
{
    use Concerns\ValidatesInput,
        Concerns\DetectsDirtyProperties,
        Concerns\HandlesActions,
        Concerns\ReceivesEvents,
        Concerns\InteractsWithProperties,
        Concerns\TracksRenderedChildren;

    public $id;
    public $redirectTo;
    protected $name;
    protected $lifecycleHooks = [
        'mount', 'updating', 'updated',
    ];

    public function name()
    {
        return $this->name ?: collect(explode('.', str_replace(['/', '\\'], '.', static::class)))
            ->diff(['App', 'Http', 'Livewire'])
            ->map([Str::class, 'kebab'])
            ->implode('.');
    }

    public function redirect($url)
    {
        $this->redirectTo = $url;
    }

    public function output($errors = null)
    {
        $view = $this->render();

        throw_unless($view instanceof View,
            new \Exception('"render" method on ['.get_class($this).'] must return instance of ['.View::class.']'));

        $dom = $view
            ->with([
                'errors' => (new ViewErrorBag)->put('default', $errors ?: new MessageBag),
                '_instance' => $this,
            ])
            // Automatically inject all public properties into the blade view.
            ->with($this->getPublicPropertiesDefinedBySubClass())
            ->render();

        // Basic minification: strip newlines and return carraiges.
        return str_replace(["\n", "\r"], '', $dom);
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
