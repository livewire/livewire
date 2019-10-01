<?php

namespace Livewire\Connection;

use Livewire\Livewire;

class HttpConnectionHandler extends ConnectionHandler
{
    public function __invoke()
    {
        return $this->handle(
            request([
                'actionQueue',
                'name',
                'children',
                'data',
                'id',
                'checksum',
                'fromPrefetch',
                'gc',
            ])
        );
    }
}
