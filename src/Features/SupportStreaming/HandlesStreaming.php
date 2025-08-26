<?php

namespace Livewire\Features\SupportStreaming;

use Livewire\ComponentHookRegistry;

trait HandlesStreaming
{
    function stream($to = null, $content = null, $replace = false, $ref = null)
    {
        if (is_null($to) && is_null($content)) {
            return new StreamManager($this);
        }

        $hook = ComponentHookRegistry::getHook($this, SupportStreaming::class);

        $hook->stream($to, $content, $replace, $ref);
    }
}
