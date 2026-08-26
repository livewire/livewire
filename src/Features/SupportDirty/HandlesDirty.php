<?php

namespace Livewire\Features\SupportDirty;

use function Livewire\store;

trait HandlesDirty
{
    /**
     * Treat the state at the end of this request as the component's new "saved"
     * state, so that `wire:dirty.persist` reports clean again. Typically called
     * at the end of a save action.
     */
    public function rebaseline()
    {
        store($this)->set('rebaseline', true);
    }
}
