<?php

namespace Livewire\Features\SupportEvents;

class Event
{
    protected $name;
    protected $ref;
    protected $params;
    protected $self;
    protected $component;
    protected $el;

    public function __construct($name, $params)
    {
        $this->name = $name;

        if (isset($params['ref'])) {
            $this->ref($params['ref']);
            unset($params['ref']);
        }

        if (isset($params['component'])) {
            $this->component($params['component']);
            unset($params['component']);
        }

        if (isset($params['el'])) {
            $this->el($params['el']);
            unset($params['el']);
        }

        if (isset($params['self'])) {
            $this->self();
            unset($params['self']);
        }

        // Handle legacy 'to' parameter for backward compatibility
        if (isset($params['to'])) {
            $this->component($params['to']);
            unset($params['to']);
        }

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

    public function ref($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    public function el($selector)
    {
        $this->el = $selector;

        return $this;
    }

    public function to($component = null, $ref = null, $el = null, $self = null)
    {
        if ($self) {
            return $this->self();
        }

        if ($ref) {
            return $this->ref($ref);
        }

        if ($el) {
            return $this->el($el);
        }

        return $this->component($component);
    }

    public function serialize()
    {
        $output = [
            'name' => $this->name,
            'params' => $this->params,
        ];

        if ($this->self) $output['self'] = true;
        if ($this->component) $output['component'] = app('livewire.factory')->resolveComponentName($this->component);
        if ($this->ref) $output['ref'] = $this->ref;
        if ($this->el) $output['el'] = $this->el;

        return $output;
    }
}
