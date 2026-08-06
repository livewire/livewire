<?php

namespace Livewire\Concerns;

trait InteractsWithLazyLoading
{
    protected function markLazyLoadMounting()
    {
        $this->storeSet('isLazyLoadMounting', true);
    }

    protected function markLazyLoadHydrating()
    {
        $this->storeSet('isLazyLoadHydrating', true);
    }

    protected function markLazyIsolated($isolate)
    {
        $this->storeSet('isLazyIsolated', $isolate);
    }

    protected function isLazyLoadMounting()
    {
        return $this->storeGet('isLazyLoadMounting', false);
    }

    protected function isLazyLoadHydrating()
    {
        return $this->storeGet('isLazyLoadHydrating', false);
    }

    protected function isLazyIsolated()
    {
        return $this->storeGet('isLazyIsolated');
    }
}