<?php

namespace Livewire\Connection;

use Livewire\Livewire;

class HttpConnectionHandler extends ConnectionHandler
{
    // This simulates extending Illuminate/Routing/Controller
    public function getMiddleware()
    {
        // Because the "middleware" is dynamically generated,
        // `php artisan route:list` will throw an error.
        // Therefore, we'll skip this for the console.
        if (app()->runningInConsole()) {
            return;
        }

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
<<<<<<< HEAD
                'browserId'
=======
                'fromPrefetch',
>>>>>>> master
            ])
        );
    }
}
