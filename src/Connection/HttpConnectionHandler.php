<?php

namespace Livewire\Connection;

use Livewire\Livewire;

class HttpConnectionHandler extends ConnectionHandler
{
    // This simulates extending Illuminate/Routeing/Controller
    public function getMiddleware()
    {
        $middleware = decrypt(request('middleware'), $unserialize = true);

        return array_map(function ($m) {
            return [
                'middleware' => $m,
                'options' => [],
            ];
        }, $middleware);
    }

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
                'browserId'
            ])
        );
    }
}
