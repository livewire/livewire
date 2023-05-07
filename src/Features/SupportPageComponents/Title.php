<?php

namespace Livewire\Features\SupportPageComponents;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class Title extends LivewireAttribute
{
    function __construct(
        public $content,
    ) {}
}
