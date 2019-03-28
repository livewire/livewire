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

    public function updating(...$params)
    {
        if (method_exists($this->wrapped, 'updating')) {
            $this->wrapped->updating(...$params);
        }
    }

    public function updated(...$params)
    {
        if (method_exists($this->wrapped, 'updated')) {
            $this->wrapped->updated(...$params);
        }
    }
}
