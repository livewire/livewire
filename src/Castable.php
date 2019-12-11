<?php

namespace Livewire;

interface Castable
{
    public function cast($value);

    public function uncast($value);
}
