<?php

namespace Livewire\Features\SupportPageComponents;

#[\Attribute]
class Layout
{
    function __construct(
        public $name,
        public $params = [],
    ) {}
}
