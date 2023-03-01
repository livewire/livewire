<?php

namespace Livewire\Mechanisms\UpdateComponents\Synthesizers;

class Seat
{
    public function __construct(
        public $parent,
        public $name,
        public $type = [],
        public $attributes = [],
    ) {}
}
