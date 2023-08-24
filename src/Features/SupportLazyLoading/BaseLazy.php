<?php

namespace Livewire\Features\SupportLazyLoading;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class BaseLazy extends LivewireAttribute
{
    function __construct(
        public $mode = true
    )
    {
    }
}
