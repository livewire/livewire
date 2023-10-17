<?php

namespace Livewire\Features\SupportPageComponents;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseTitle extends LivewireAttribute
{
    public function __construct(
        public $content,
    ) {
    }
}
