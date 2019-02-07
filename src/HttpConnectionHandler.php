<?php

namespace Livewire;

use Livewire\Livewire;

class HttpConnectionHandler extends ConnectionHandler
{
    public function __invoke()
    {
        return $this->handle(request()->all());
    }
}
