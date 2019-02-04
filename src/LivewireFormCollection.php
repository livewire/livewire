<?php

namespace Livewire;

use Illuminate\Support\Collection;

class LivewireFormCollection extends Collection
{
    public function add($name, $rules)
    {
        $this->offsetSet($name, $form = new LivewireForm($rules));

        return $form;
    }

    public function __get($value)
    {
        return $this->offsetGet($value);
    }
}
