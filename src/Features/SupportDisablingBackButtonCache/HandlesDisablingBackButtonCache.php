<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Livewire\Mechanisms\DataStore;

trait HandlesDisablingBackButtonCache
{
    function disableBackButtonCache()
    {
        SupportDisablingBackButtonCache::$disableBackButtonCache = true;
    }

    function enableBackButtonCache()
    {
        SupportDisablingBackButtonCache::$disableBackButtonCache = false;
    }
}
