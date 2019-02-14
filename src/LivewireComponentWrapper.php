<?php

namespace Livewire;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

class LivewireComponentWrapper
{
    use Concerns\TracksDirtySyncedInputs,
        Concerns\HasLifecycleHooks,
        Concerns\TracksChildren,
        Concerns\ReceivesEvents;

    public $wrapped;

    public function __construct($wrapped)
    {
        $this->wrapped = $wrapped;

        $this->hashCurrentObjectPropertiesForEasilyDetectingChangesLater();
    }

    public static function wrap($wrapped)
    {
        return new static($wrapped);
    }

    public function output($errors = null)
    {
        return $this->trackChildrenBeingMounted(function () use ($errors) {
            return $this->wrapped->render()->with([
                'errors' => (new ViewErrorBag)
                    ->put('default', $errors ?: new MessageBag),
                'wrapper' => $this,
            ])->render();
        });
    }
}
