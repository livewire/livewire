<?php

namespace Livewire\Features\SupportComputed;

class DefaultPersistHandler extends ComputedHandler
{
    protected function generateKey()
    {
        if ($this->computed->key) return $this->computed->key;

        return $this->replaceDynamicPlaceholders('lw_computed.'.$this->computed->getComponent()->getId().'.'.$this->computed->getName());
    }
}
