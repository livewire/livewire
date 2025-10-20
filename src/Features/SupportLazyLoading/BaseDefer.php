<?php

namespace Livewire\Features\SupportLazyLoading;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class BaseDefer extends LivewireAttribute
{
    public function __construct(
        public $isolate = true
    ) {}
}
