<?php

namespace Livewire\Attributes;

use Livewire\Features\SupportDebounce\BaseDebounce;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class Debounce extends BaseDebounce
{
    //
}