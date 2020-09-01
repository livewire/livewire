<?php

namespace Livewire\Testing\Concerns;

trait HasFunLittleUtilities
{
    public function dump()
    {
        echo $this->lastRenderedDom;

        return $this;
    }

    public function tap($callback)
    {
        $callback($this);

        return $this;
    }
}
