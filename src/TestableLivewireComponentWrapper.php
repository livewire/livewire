<?php

namespace Livewire;

use Livewire\LivewireComponent;

class TestableLivewireComponentWrapper extends LivewireComponentWrapper
{
    public function mountChild($internalKey, $componentName, ...$options)
    {
        $dom = '<div>not-mounted because testing</div>';
        $id = 'n/a';
        $serialized = 'n/a';

        return [$dom, $id, $serialized];
    }
}
