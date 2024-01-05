<?php

namespace Livewire\Mechanisms\HandleComponents;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseView extends LivewireAttribute
{
    function __construct(
        public $name,
        public $params = [],
    ) {}
}
