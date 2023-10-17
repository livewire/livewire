<?php

namespace Livewire\Features\SupportStreaming;

use Livewire\ComponentHookRegistry;

trait HandlesStreaming
{
    public function stream($to, $content, $replace = false)
    {
        $hook = ComponentHookRegistry::getHook($this, SupportStreaming::class);

        $hook->stream($to, $content, $replace);
    }
}
