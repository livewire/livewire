<?php

namespace Livewire\Features\SupportStacks;

use Livewire\ComponentHookRegistry;

trait HandlesStacks
{
    function addToStack($stack, $type, $contents, $key = null)
    {
        $hook = ComponentHookRegistry::getHook($this, SupportStacks::class);

        $hook->addToStack($stack, $type, $contents, $key);
    }
}