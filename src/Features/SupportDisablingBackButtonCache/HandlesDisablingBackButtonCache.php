<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Livewire\Mechanisms\ComponentDataStore;

trait HandlesDisablingBackButtonCache
{
    function disableBackButtonCache()
    {
        SupportDisablingBackButtonCache::$disableBackButtonCache = true;
    }
}
