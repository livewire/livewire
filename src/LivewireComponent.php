<?php

namespace Livewire;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

abstract class LivewireComponent
{
    use Concerns\TracksDirtySyncedInputs,
        Concerns\HasLifecycleHooks,
        Concerns\CanBeSerialized,
        Concerns\ReceivesEvents,
        Concerns\ValidatesInput;

    public $id;
    public $prefix;

    public function __construct($id, $prefix)
    {
        $this->id = $id;
        $this->prefix = $prefix;
    }

    abstract public function render();

    public function output($errors = null)
    {
        $dom = $this->render()->with([
            'errors' => (new ViewErrorBag)
                ->put('default', $errors ?: new MessageBag),
        ])->render();

        return $this->attachIdToRootNode($dom);
    }

    public function attachIdToRootNode($rawDom)
    {
        return preg_replace(
            '/(<[a-zA-Z0-9\-]*)/',
            sprintf('$1 %s:root-id="%s"', $this->prefix, $this->id),
            $rawDom,
            $limit = 1
        );
    }

    public function getPropertyValue($prop) {
        // This is used by the test wrapper. Otherwise,
        // users would have to declare props as "public".
        return $this->{$prop};
    }
}
