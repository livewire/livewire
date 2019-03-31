<?php

namespace Livewire;

use BadMethodCallException;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

abstract class LivewireComponent
{
    use Concerns\ValidatesInput,
        Concerns\TracksDirtySyncedInputs,
        Concerns\HandlesActions,
        Concerns\InteractsWithProperties;

    public $id;
    public $redirectTo;

    protected $lifecycleHooks = [
        'created', 'updated', 'updating',
    ];

    public function __construct($id)
    {
        $this->id = $id;

        $this->hashComponentPropertiesForDetectingFutureChanges();
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

        return $view
            ->with([
                'errors' => (new ViewErrorBag)->put('default', $errors ?: new MessageBag),
            ])
            // Automatically inject all public properties into the blade view.
            ->with($this->getPublicPropertiesDefinedBySubClass())
            ->render();
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
