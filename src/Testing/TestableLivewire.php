<?php

namespace Livewire\Testing;

use Livewire\Connection\ComponentHydrator;

class TestableLivewire
{
    public $name;
    public $id;
    public $children;
    public $prefix;
    public $instance;
    public $dom;
    public $data;
    public $dirtyInputs;
    public $listeningFor;

    use Concerns\HasFunLittleUtilities,
        Concerns\MakesCallsToComponent;

    public function __construct($name, $prefix)
    {
        $this->prefix = $prefix;

        // This allows the user to test a component by it's class name,
        // and not have to register an alias.
        if (class_exists($name)) {
            $componentClass = $name;
            app('livewire')->component($name = str_random(20), $componentClass);
        }

        $result = app('livewire')->mount($this->name = $name);

        $this->checksum = $result->checksum;

        $this->initialUpdateComponent($result);
    }

    public function initialUpdateComponent($output)
    {
        $this->id = $output->id;
        $this->dom = $output->toHtml();
        $this->data = $output->data;
        $this->children = $output->children;
        $this->listeningFor = $output->listeningFor;
        $this->instance = ComponentHydrator::hydrate($this->name, $this->id, $this->data, $this->checksum);
    }

    public function updateComponent($output)
    {
        $this->id = $output->id;
        $this->dom = $output->dom;
        $this->data = $output->data;
        $this->children = $output->children;
        $this->dirtyInputs = $output->dirtyInputs;
        $this->listeningFor = $output->listeningFor;
        $this->eventQueue = $output->eventQueue;
        $this->instance = ComponentHydrator::hydrate($this->name, $this->id, $this->data, $this->checksum);
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
