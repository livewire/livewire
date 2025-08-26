<?php

namespace Livewire\Features\SupportEvents;

use Livewire\Mechanisms\ComponentRegistry;

class Event
{
    protected $name;
    protected $ref;
    protected $params;
    protected $self;
    protected $component;

    public function __construct($name, $params)
    {
        $this->name = $name;
        $this->params = $params;

        if (isset($params['ref'])) {
            $this->ref($params['ref']);

            unset($params['ref']);
        }

        if (isset($params['self'])) {
            $this->self();

            unset($params['self']);
        }

        if (isset($params['to'])) {
            $this->component($params['to']);

            unset($params['to']);
        }
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

    public function ref($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    public function to($name = null, $ref = null)
    {
        if ($ref) {
            return $this->ref($ref);
        }

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
        if ($this->ref) $output['ref'] = $this->ref;

        return $output;
    }
}
