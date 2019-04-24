<?php

namespace Livewire\Testing;

use Livewire\Connection\ComponentHydrator;

class TestableLivewire
{
    public $component;
    public $id;
    public $prefix;
    public $instance;
    public $dom;
    public $data;
    public $dirtyInputs;
    public $listeningFor;

    use Concerns\HasFunLittleUtilities,
        Concerns\MakesCallsToComponent;

    public function __construct($component, $prefix)
    {
        $this->prefix = $prefix;

        if (class_exists($component)) {
            // This allows the user to test a component by it's class name,
            // and not have to register an alias.
            $componentClass = $component;
            app('livewire')->component($component = str_random(20), $componentClass);
        }

        $this->updateComponent(
            app('livewire')->mount($this->component = $component)
        );
    }

    public function updateComponent($output)
    {
        $this->id = $output->id;
        $this->dom = $output->dom;
        $this->data = $output->data;
        $this->dirtyInputs = $output->dirtyInputs;
        $this->listeningFor = $output->listeningFor;
        $this->eventQueue = $output->eventQueue;
        $this->instance = ComponentHydrator::hydrate($this->component, $this->data);
    }

    public function __get($property)
    {
        return $this->instance->{$property};
    }

    public function __call($method, $params)
    {
        return $this->runAction($method, $params);
    }
}
