<?php

namespace Livewire\Testing;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestableLivewire
{
    public $prefix;
    public $payload = [];
    public $componentName;
    public $lastValidator;
    public $lastHttpException;

    use Concerns\MakesAssertions,
        Concerns\MakesCallsToComponent,
        Concerns\HasFunLittleUtilities;

    public function __construct($name, $prefix, $params = [])
    {
        $this->prefix = $prefix;

        // This allows the user to test a component by it's class name,
        // and not have to register an alias.
        if (class_exists($name)) {
            $componentClass = $name;
            app('livewire')->component($name = Str::random(20), $componentClass);
        }

        try {
            $result = app('livewire')->mount($this->componentName = $name, ...$params);

            $this->updateComponent($result);
        } catch (HttpException $exception) {
            $this->lastHttpException = $exception;
        }
    }

    public function updateComponent($output)
    {
        $this->payload = [
            'id' => $output->id,
            'dom' => $output->dom,
            'data' => $output->data,
            'children' => $output->children,
            'events' => $output->events,
            'eventQueue' => $output->eventQueue,
            'errorBag' => $output->errorBag,
            'checksum' => $output->checksum,
            'redirectTo' => $output->redirectTo,
            'dirtyInputs' => $output->dirtyInputs,
        ];
    }

    public function id()
    {
        return $this->payload['id'];
    }

    public function instance()
    {
        return Livewire::activate($this->componentName, $this->id());
    }

    public function get($property)
    {
        return data_get($this->payload['data'], $property);
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
