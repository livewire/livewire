<?php

namespace Livewire\Features\SupportPageComponents;

#[\Attribute]
class Title
{
    function __construct(
        public $content,
    ) {}
}
