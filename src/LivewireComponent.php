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
    protected $children = [];
    protected $mountedChildren = [];

    public function __construct($id, $prefix)
    {
        $this->id = $id;
        $this->prefix = $prefix;
    }

    abstract public function render();

    public function output($errors = null)
    {
        $this->mountedChildren = [];

        $dom = $this->render()->with([
            'errors' => (new ViewErrorBag)
                ->put('default', $errors ?: new MessageBag),
            'livewire' => $this,
        ])->render();

        // This allows us to recognize when a previosuly rendered child,
        // is no longer being rendered, we can clear their "children"
        // entry so that we don't still return dummy data.
        foreach ($this->children as $childName => $id) {
            if (! in_array($childName, $this->mountedChildren)) {
                unset($this->children[$childName]);
            }
        }

        return $dom;
    }

    public function mountChild($componentName)
    {
        $this->mountedChildren[] = $componentName;

        // Note: this only allows for one child component of each type in a component.
        if ($id = $this->children[$componentName] ?? false) {
            return [
                // The "id" is included here as a key for morphdom.
                sprintf('<div wire:root="%s" id="%s">no-content</div>', $id, $id),
                $id,
                'not-serialized',
            ];
        }

        [$dom, $id, $serialized] = app('livewire')->mount($componentName);

        $this->children[$componentName] = $id;

        return [$dom, $id, $serialized];
    }

    public function getPropertyValue($prop) {
        // This is used by the test wrapper. Otherwise,
        // users would have to declare props as "public".
        return $this->{$prop};
    }
}
