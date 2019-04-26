<?php

namespace Livewire;

use BadMethodCallException;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

abstract class LivewireComponent
{
    // @todo - move the child tracking logic into trait.
    public $renderedChildren = [];
    public $previouslyRenderedChildren = [];

    public function getRenderedChildComponentId($id)
    {
        return $this->previouslyRenderedChildren[$id];
    }

    public function logRenderedChild($id, $componentId)
    {
        $this->renderedChildren[$id] = $componentId;
    }

    public function preserveRenderedChild($id)
    {
        $this->renderedChildren[$id] = $this->previouslyRenderedChildren[$id];
    }

    public function childHasBeenRendered($id)
    {
        return in_array($id, array_keys($this->previouslyRenderedChildren));
    }

    public function setPreviouslyRenderedChildren($children)
    {
        $this->previouslyRenderedChildren = $children;
    }

    public function getRenderedChildren()
    {
        return $this->renderedChildren;
    }

    use Concerns\ValidatesInput,
        Concerns\DetectsDirtyProperties,
        Concerns\HandlesActions,
        Concerns\ReceivesEvents,
        Concerns\InteractsWithProperties;

    public $id;
    public $redirectTo;

    protected $lifecycleHooks = [
        'mount', 'updating', 'updated',
    ];

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
            return;
        }


        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
