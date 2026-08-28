<?php

namespace Livewire\Features\SupportDirty;

use function Livewire\store;

trait HandlesDirty
{
    public function markAsClean()
    {
        store($this)->set('markClean', true);
    }
}
