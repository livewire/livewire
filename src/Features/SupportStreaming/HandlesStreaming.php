<?php

namespace Livewire\Features\SupportStreaming;

use Livewire\ComponentHookRegistry;

trait HandlesStreaming
{
    function stream($content = null, $replace = false, $name = null, $el = null, $ref = null, $to = null)
    {
        // Handle legacy 'to' parameter for backward compatibility...
        if ($to) $name = $to;

        $hook = ComponentHookRegistry::getHook($this, SupportStreaming::class);

        $stream = new StreamManager($this, $hook);

        if (! $content) {
            return $stream;
        }

        if (! $el && ! $ref && ! $name) {
            return $stream->content($content, $replace);
        }

        return $stream->content($content, $replace)->to($name, $el, $ref);
    }
}
