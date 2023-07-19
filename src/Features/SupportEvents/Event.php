<?php

namespace Livewire\Features\SupportEvents;

use Livewire\Mechanisms\ComponentRegistry;

class Event
{
    protected $name;
    protected $params;
    protected $self;
    protected $component;

    public function __construct($name, $params)
    {
        $this->name = $name;
        $this->params = $params;
    }

    public function self()
    {
        $this->self = true;

        return $this;
    }

    public function component($name)
    {
        $this->component = $name;

        return $this;
    }

    public function to($name)
    {
        return $this->component($name);
    }

    public function serialize()
    {
        $output = [
            'name' => $this->name,
            'params' => $this->params,
        ];

        if ($this->self) $output['self'] = true;
        if ($this->component) $output['to'] = app(ComponentRegistry::class)->getName($this->component);

        return $output;
    }
}
