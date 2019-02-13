<?php

namespace Livewire\Concerns;

trait ReceivesEvents
{
    public function syncInput($name, $value)
    {
        if (method_exists($this, 'onSync' . studly_case($name))) {
            $this->{'onSync' . studly_case($name)}($value);
        }

        $this->removeFromDirtyPropertiesList($name);

        $this->{$name} = $value;
    }

    public function fireMethod($method, $params)
    {
        $this->wrapped->{$method}(...$params);
    }
}
