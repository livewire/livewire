<?php

namespace Livewire;

abstract class LivewireComponent
{
    protected $connection;
    protected $component;

    public function __construct($connection, $component)
    {
        $this->connection = $connection;
        $this->component = $component;
    }

    abstract public function render();

    public function mounted()
    {
        //
    }

    public function sync($model, $value)
    {
        if (method_exists($this, 'onSync' . studly_case($model))) {
            $this->{'onSync' . studly_case($model)}($value);
        }

        $this->{$model} = $value;
    }

    public function refresh()
    {
        $this->connection->send(json_encode([
            'component' => $this->component,
            'dom' => $this->render()->render(),
        ]));
    }

    public function __toString()
    {
        return $this->render();
    }
}
