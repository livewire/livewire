<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Livewire\SocketConnectionHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class LivewireStartCommand extends Command
{
    protected $signature = 'livewire:start';

    protected $description = '@todo';

    public function handle()
    {
        IoServer::factory(
            new HttpServer(
                new WsServer(
                    new SocketConnectionHandler($this)
                )
            ),
            8080
        )->run();
    }
}
