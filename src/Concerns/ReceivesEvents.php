<?php

namespace Livewire\Concerns;

trait ReceivesEvents
{
    public function syncInput($name, $value)
    {
        $this->callBeforeAndAferSyncHooks($name, $value, function ($name, $value) {
            $this->removeFromDirtyInputsList($name);

            $this->wrapped->setPropertyValue($name, $value);
        });
    }

    public function lazySyncInput($name, $value)
    {
        $this->callBeforeAndAferSyncHooks($name, $value, function ($name, $value) {
            $this->wrapped->setPropertyValue($name, $value);

            $this->rehashProperty($name);
        });
    }

    protected function callBeforeAndAferSyncHooks($name, $value, $callback)
    {
        // Sticking with the "beforeX", "Xed" naming convention.
        $beforeMethod = 'before' . studly_case($name) . 'Sync';
        $afterMethod = camel_case($name) . 'Synced';

        if (method_exists($this->wrapped, $beforeMethod)) {
            $this->wrapped->{$beforeMethod}($value);
        }

        $callback($name, $value);

        if (method_exists($this->wrapped, $afterMethod)) {
            $this->wrapped->{$afterMethod}($value);
        }
    }

    public function fireEvent($componentId, $event, $params)
    {
        $this->callMethod(
            $this->listeners($componentId)[$event],
            $params
        );
    }

    public function callMethod($method, $params = [])
    {
        if ($method === '$set') {
            $prop = array_shift($params);
            $this->syncInput($prop, head($params));
            return;
        }

        $this->wrapped->{$method}(...$params);
    }
}
