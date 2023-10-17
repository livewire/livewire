<?php

namespace Livewire\Features\SupportPageComponents;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseLayout extends LivewireAttribute
{
    public function __construct(
        public $name,
        public $params = [],
    ) {
    }
}
