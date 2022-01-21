<?php

namespace Livewire;

interface LivewirePropertyType
{
    public function hydrate($instance, $name, $value);

    public function dehydrate($instance, $name, $value);
}
