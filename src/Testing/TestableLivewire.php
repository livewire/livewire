<?php

namespace Livewire\Testing;

use Livewire\Connection\ComponentHydrator;

class TestableLivewire
{
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

        $this->updateComponent(
            app('livewire')->mount($component)
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
        $this->instance = ComponentHydrator::hydrate($this->data);
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
