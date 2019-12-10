<?php

namespace Livewire\Testing;

use Illuminate\Support\Str;
use Livewire\Livewire;

class TestableLivewire
{
    public $componentName;
    public $id;
    public $children;
    public $checksum;
    public $prefix;
    public $dom;
    public $data;
    public $dirtyInputs;
    public $events;
    public $eventQueue;
    public $errorBag;
    public $redirectTo;
    public $lastValidator;

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

        $result = app('livewire')->mount($this->componentName = $name, ...$params);

        $this->initialUpdateComponent($result);
    }

    public function initialUpdateComponent($output)
    {
        $this->id = $output->id;
        $this->dom = $output->dom;
        $this->data = $output->data;
        $this->children = $output->children;
        $this->events = $output->events;
        $this->eventQueue = $output->eventQueue;
        $this->errorBag = $output->errorBag;
        $this->checksum = $output->checksum;
        $this->redirectTo = $output->redirectTo;
    }

    public function updateComponent($response)
    {
        $output = $response->toArray();

        $this->id = $output['id'];
        $this->dom = $output['dom'];
        $this->data = $output['data'];
        $this->checksum = $output['checksum'];
        $this->children = $output['children'];
        $this->dirtyInputs = $output['dirtyInputs'];
        $this->events = $output['events'];
        $this->redirectTo = $output['redirectTo'];
        $this->eventQueue = $output['eventQueue'];
        $this->errorBag = $output['errorBag'] ?? [];
    }

    public function instance()
    {
        return Livewire::activate($this->componentName, $this->id);
    }

    public function get($property)
    {
        $cachedProtectedProperties = data_get(cache()->get("{$this->id}"), '__protected_properties', []);

        return data_get($this->data + $cachedProtectedProperties, $property);
    }

    public function __get($property)
    {
        return $this->get($property);
    }

    public function __call($method, $params)
    {
        return $this->call($method, $params);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }
}
