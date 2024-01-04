<?php

namespace Livewire\Features\SupportEvents;

use Livewire\Mechanisms\ComponentRegistry;

class Event
{
    protected $name;
    protected $params;
    protected $self;
    protected $components;

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

    public function components($names)
    {
        $this->components = $names;

        return $this;
    }

    public function to(...$names)
    {
        return $this->components($names);
    }

    public function serialize()
    {
        $output = [
            'name' => $this->name,
            'params' => $this->params,
        ];

        if ($this->self) $output['self'] = true;
        if ($this->components) $output['to'] = app(ComponentRegistry::class)->getName($this->components);

        return $output;
    }
}
