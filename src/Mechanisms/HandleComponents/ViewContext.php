<?php

namespace Livewire\Mechanisms\HandleComponents;

use function Livewire\invade;

class ViewContext
{
    function __construct(
        public $slots = [],
        public $pushes = [],
        public $prepends = [],
        public $sections = [],
    ) {}

    function extractFromEnvironment($__env)
    {
        $factory = invade($__env);

        $this->slots = $factory->slots;
        $this->pushes = $factory->pushes;
        $this->prepends = $factory->prepends;
        $this->sections = $factory->sections;
    }

    function mergeIntoNewEnvironment($__env)
    {
        $factory = invade($__env);

        $factory->slots = $this->slots;
        $factory->pushes = $this->pushes;
        $factory->prepends = $this->prepends;
        $factory->sections = $this->sections;
    }
}
