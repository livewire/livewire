<?php

namespace Livewire\Features\SupportStreaming;

use Livewire\ComponentHookRegistry;

trait HandlesStreaming
{
    function stream($name, $content, $append = false)
    {
        $hook = ComponentHookRegistry::getHook($this, SupportStreaming::class);

        $hook->stream($name, $content, $append);
    }
}
