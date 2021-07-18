<?php

namespace Livewire;

trait Wireable
{
    public function toLivewire()
    {
        return serialize($this);
    }

    public static function fromLivewire($value): self
    {
        return unserialize($value);
    }
}
