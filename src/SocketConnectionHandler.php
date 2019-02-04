<?php

namespace Livewire;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class SocketConnectionHandler extends ConnectionHandler implements MessageComponentInterface
{
    protected $output;
    protected $clients;
    protected $liveViewsByResourceId;

    public function __construct($console)
    {
        $this->output = $console;
        $this->clients = new \SplObjectStorage;
        $this->liveViewsByResourceId = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->liveViewsByResourceId[$conn->resourceId] = [];
        $this->clients->attach($conn);

        $this->output->info("Connection opened: ({$conn->resourceId})");
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $stuff = json_decode($msg, $asArray = true);
        $component = $stuff['component'];
        $event = $stuff['event'];

        if (! isset($this->liveViewsByResourceId[$from->resourceId][$component])) {
            $this->liveViewsByResourceId[$from->resourceId][$component] = Livewire::activate($component, $from);
        }

        $this->output->info("Event received: ({$event})");

        $from->send(json_encode($this->handle($stuff, $this->liveViewsByResourceId[$from->resourceId][$component])));
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        $this->output->info("Connection closed: ({$conn->resourceId})");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
        dd($e);

        $this->output->warn("Error: ({$e->getMessage()})");
    }
}
