<?php

namespace Livewire\Concerns;

use BadMethodCallException;

trait HasLifecycleHooks
{
    public function created(...$params)
    {
        if (method_exists($this->wrapped, 'created')) {
            $this->wrapped->created(...$params);
        }
    }

    public function mounted(...$params)
    {
        if (method_exists($this->wrapped, 'mounted')) {
            $this->wrapped->mounted(...$params);
        }
    }

    public function beforeUpdate(...$params)
    {
        if (method_exists($this->wrapped, 'beforeUpdate')) {
            $this->wrapped->beforeUpdate(...$params);
        }
    }

    public function updated(...$params)
    {
        if (method_exists($this->wrapped, 'updated')) {
            $this->wrapped->updated(...$params);
        }
    }
}
