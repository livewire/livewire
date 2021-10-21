<?php

namespace Livewire\Connection;

use Livewire\LifecycleManager;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        return LifecycleManager::fromSubsequentRequest($payload)
            ->boot()
            ->hydrate()
            ->renderToView()
            ->dehydrate()
            ->toSubsequentResponse();
    }
}
