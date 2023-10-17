<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

trait HandlesDisablingBackButtonCache
{
    public function disableBackButtonCache()
    {
        SupportDisablingBackButtonCache::$disableBackButtonCache = true;
    }

    public function enableBackButtonCache()
    {
        SupportDisablingBackButtonCache::$disableBackButtonCache = false;
    }
}
