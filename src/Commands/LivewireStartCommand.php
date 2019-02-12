<?php

namespace Livewire\Commands;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Illuminate\Console\Command;
use Livewire\SocketConnectionHandler;
use Livewire\Connection\WebSocketConnectionHandler;

class LivewireStartCommand extends Command
{
    protected $signature = 'livewire:start';

    protected $description = '@todo';

    public function handle()
    {
        IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketConnectionHandler($this)
                )
            ),
            8080
        )->run();
    }
}
