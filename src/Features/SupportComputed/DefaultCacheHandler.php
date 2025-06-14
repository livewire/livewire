<?php

namespace Livewire\Features\SupportComputed;

class DefaultCacheHandler extends ComputedHandler
{
    protected function generateKey()
    {
        return $this->replaceDynamicPlaceholders('lw_computed.'.$this->computed->getComponent()->getName().'.'.$this->computed->getName());
    }
}
