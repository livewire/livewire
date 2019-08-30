<?php

namespace Livewire\Testing;

use Illuminate\Support\Str;
use Livewire\Connection\ComponentHydrator;

class TestableLivewire
{
    public $name;
    public $id;
    public $children;
    public $checksum;
    public $prefix;
    public $instance;
    public $dom;
    public $data;
    public $dirtyInputs;
    public $events;
    public $eventQueue;
    public $redirectTo;

    use Concerns\HasFunLittleUtilities,
        Concerns\MakesCallsToComponent,
        Concerns\MakesAssertions;

    public function __construct($name, $prefix, $params = [])
    {
        $this->prefix = $prefix;

        // This allows the user to test a component by it's class name,
        // and not have to register an alias.
        if (class_exists($name)) {
            $componentClass = $name;
            app('livewire')->component($name = Str::random(20), $componentClass);
        }

        $result = app('livewire')->mount($this->name = $name, ...$params);

        $this->initialUpdateComponent($result);
    }

    public function initialUpdateComponent($output)
    {
        $this->id = $output->id;
        $this->dom = $output->toHtml();
        $this->data = $output->data;
        $this->children = $output->children;
        $this->events = $output->events;
        $this->checksum = $output->checksum;
        $this->instance = ComponentHydrator::hydrate($this->name, $this->id, $this->data, $this->checksum);
    }

    public function updateComponent($output)
    {
        $this->id = $output->id;
        $this->dom = $output->dom;
        $this->data = $output->data;
        $this->checksum = $output->checksum;
        $this->children = $output->children;
        $this->dirtyInputs = $output->dirtyInputs;
        $this->events = $output->events;
        $this->redirectTo = $output->redirectTo;
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

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }
}
