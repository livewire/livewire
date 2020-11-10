<?php

namespace Livewire\Connection;

use Livewire\LifecycleManager;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        return LifecycleManager::fromSubsequentRequest($payload)
            ->hydrate()
            ->renderToView()
            ->dehydrate()
            ->toSubsequentResponse();
    }
}
