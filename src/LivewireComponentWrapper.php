<?php

namespace Livewire;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Livewire\LivewireComponent;

class LivewireComponentWrapper
{
    use Concerns\TracksDirtySyncedInputs,
        Concerns\HasLifecycleHooks,
        Concerns\TracksChildren,
        Concerns\ReceivesEvents;

    public $wrapped;
    protected $currentChildInView;
    protected $queuedListeners = [];

    public function __construct($wrapped)
    {
        $this->wrapped = $wrapped;

        $this->hashCurrentObjectPropertiesForEasilyDetectingChangesLater();
    }

    public static function wrap($wrapped)
    {
        return new static($wrapped);
    }

    public function setCurrentChildInView($id)
    {
        $this->currentChildInView = $id;
    }

    public function prepareListenerForRegistration($event, $action)
    {
        $this->queuedListeners[$event] = $action;
    }

    public function registerListeners()
    {
        if (count($this->queuedListeners)) {
            $this->wrapped->listenersByChildComponentId[$this->currentChildInView] = $this->queuedListeners;
            $this->queuedListeners = [];
        }

        $this->currentChildInView = null;
    }

    public function listeners($componentId = null)
    {
        return $componentId
            ? $this->wrapped->listenersByChildComponentId[$componentId]
            : $this->wrapped->listenersByChildComponentId;
    }

    public function output($errors = null)
    {
        return $this->trackChildrenBeingMounted(function () use ($errors) {
            return $this->wrapped->render()->with([
                'errors' => (new ViewErrorBag)
                    ->put('default', $errors ?: new MessageBag),
                'wrapped' => $this,
            ])->with($this->getLivewireComponentPublicPropertiesAndValues())->render();
        });
    }

    public function getLivewireComponentPublicPropertiesAndValues()
    {
        $data = [];
        foreach ((new \ReflectionClass($this->wrapped))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->getDeclaringClass()->getName() !== LivewireComponent::class) {
                $data[$property->getName()] = $property->getValue($this->wrapped);
            }
        }
        return $data;
    }

    public function __get($property)
    {
        return $this->wrapped->{$property};
    }
}
