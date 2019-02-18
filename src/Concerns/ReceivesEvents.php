<?php

namespace Livewire\Concerns;

trait ReceivesEvents
{
    public function syncInput($name, $value)
    {
        if (method_exists($this->wrapped, 'onSync' . studly_case($name))) {
            $this->wrapped->{'onSync' . studly_case($name)}($value);
        }

        $this->removeFromDirtyPropertiesList($name);

        $this->wrapped->setPropertyValue($name, $value);
    }

    public function fireMethod($method, $params = [])
    {
        $this->wrapped->{$method}(...$params);
    }
}
