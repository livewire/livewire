<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

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
