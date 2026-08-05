<?php

namespace Livewire\Features\SupportLazyLoading;

use function Livewire\store;

trait HandlesLazyLoading
{
    public function lazyLoadMounting()
    {
        store($this)->set('isLazyLoadMounting', true);
    }

    public function lazyLoadHydrating()
    {
        store($this)->set('isLazyLoadHydrating', true);
    }

    public function isLazyLoadMounting()
    {
        return store($this)->get('isLazyLoadMounting', false);
    }

    public function isLazyLoadHydrating()
    {
        return store($this)->get('isLazyLoadHydrating', false);
    }
}