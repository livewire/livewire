<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class BaseQueryString extends LivewireAttribute
{
    function __construct(
        public $enabled = true
    ) {}
}
