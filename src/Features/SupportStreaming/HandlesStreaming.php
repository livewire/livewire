<?php

namespace Livewire\Features\SupportStreaming;

use Livewire\ComponentHookRegistry;

trait HandlesStreaming
{
    function stream($to, $content, $replace = false)
    {
        $hook = ComponentHookRegistry::getHook($this, SupportStreaming::class);

        $hook->stream($to, $content, $replace); }
}
