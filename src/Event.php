<?php

namespace Livewire;

class Event
{
    protected $name;
    protected $params;
    protected $up;
    protected $self;
    protected $component;

    public function __construct($name, $params)
    {
        $this->name = $name;
        $this->params = $params;
    }

    public function up()
    {
        $this->up = true;

        return $this;
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

    public function to()
    {
        return $this;
    }

    public function serialize()
    {
        $output = [
            'event' => $this->name,
            'params' => $this->params,
        ];

        if ($this->up) $output['ancestorsOnly'] = true;
        if ($this->self) $output['selfOnly'] = true;
        if ($this->component) {
            try {
                if (app($this->component) instanceof Component) {
                    info('entrou');
                    $component = Str::afterLast($this->component, '\\');
                    $this->component = Str::lower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $component));
                }
            } catch (\Exception $exception) {
                //
            }
            
            $output['to'] = $this->component;
        }

        return $output;
    }
}
