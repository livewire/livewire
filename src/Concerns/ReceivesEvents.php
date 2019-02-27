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

        if (method_exists($this->wrapped, $method = 'after' . studly_case($name) . 'Synced')) {
            $this->wrapped->{$method}($value);
        }
    }

    public function lazySyncInput($name, $value)
    {
        if (method_exists($this->wrapped, 'onSync' . studly_case($name))) {
            $this->wrapped->{'onSync' . studly_case($name)}($value);
        }

        $this->wrapped->setPropertyValue($name, $value);

        $this->rehashProperty($name);

        if (method_exists($this->wrapped, $method = 'after' . studly_case($name) . 'Synced')) {
            $this->wrapped->{$method}($value);
        }
    }

    public function fireEvent($componentId, $event, $params)
    {
        $this->fireMethod(
            $this->listeners($componentId)[$event],
            $params
        );
    }

    public function fireMethod($method, $params = [])
    {
        $this->wrapped->{$method}(...$params);
    }
}
