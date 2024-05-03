<?php

namespace Livewire\Features\SupportCustomEndpoint;

use Livewire\ComponentHook;

class SupportCustomEndpoint extends ComponentHook
{
    function dehydrate($context)
    {
        $component = $this->component;
        $member = 'endpoint';

        if (method_exists($component, $member)) {
            $context->addMemo('endpoint', $component->{$member}());
        }
    }
}
