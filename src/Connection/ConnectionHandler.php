<?php

namespace Livewire\Connection;

use Illuminate\Routing\Controller;
use Livewire\LifecycleManager;

abstract class ConnectionHandler extends Controller
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
