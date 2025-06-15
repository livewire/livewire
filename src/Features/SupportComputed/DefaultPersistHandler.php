<?php

namespace Livewire\Features\SupportComputed;

class DefaultPersistHandler extends ComputedHandler
{
    protected function generateKey()
    {
        return 'lw_computed.'.$this->computed->getComponent()->getId().'.'.$this->computed->getName();
    }
}
