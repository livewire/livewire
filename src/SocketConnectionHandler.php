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

    public function __construct($console)
    {
        $this->output = $console;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->output->info("Connection opened: ({$conn->resourceId})");
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $stuff = json_decode($msg, $asArray = true);

        $this->output->info("Event received: ({$stuff['event']})");

        $from->send(json_encode($this->handle($stuff)));
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->output->info("Connection closed: ({$conn->resourceId})");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
        dd($e);

        $this->output->warn("Error: ({$e->getMessage()})");
    }
}
