<?php

namespace Livewire\Connection;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WebSocketConnectionHandler extends ConnectionHandler implements MessageComponentInterface
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
        $payload = json_decode($msg, $asArray = true);

        $this->output->info("Event received: ({$payload['event']})");

        $from->send(json_encode($this->handle(
            $payload['event'],
            $payload['data'],
            $payload['serialized']
        )));
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->output->info("Connection closed: ({$conn->resourceId})");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // Not sure if closing the connection after an error is best or not?
        // $conn->close();

        $this->output->warn("Error: ({$e->getMessage()})");
    }
}
