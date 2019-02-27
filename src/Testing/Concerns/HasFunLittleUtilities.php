<?php

namespace Livewire\Testing\Concerns;

trait HasFunLittleUtilities
{
    public function dump()
    {
        echo $this->rawHtml;

        return $this;
    }

    public function tap($callback)
    {
        $callback($this);

        return $this;
    }

    public function fromView($nameOfViewVariable, $callback)
    {
        $callback($this->component->view()->{$nameOfViewVariable});

        return $this;
    }
}
