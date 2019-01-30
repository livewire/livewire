<?php

namespace Livewire;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class SocketHandler implements MessageComponentInterface
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
        $stuff = json_decode($msg);
        $event = $stuff->event ?? 'init';
        $payload = $stuff->payload;
        $component = $stuff->component;

        $this->output->info("Event received: ({$event})");

        switch ($event) {
            case 'init':
                $this->liveViewsByResourceId[$from->resourceId][$component] = $livewire = Livewire::activate($component, $from);
                $livewire->mounted();
                break;
            case 'sync':
                $this->liveViewsByResourceId[$from->resourceId][$component]->sync($payload->model, $payload->value);
                // // If we don't return early we cost too much in rendering AND break input elements for some reason.
                // return;
                break;
            case 'fireMethod':
                $this->liveViewsByResourceId[$from->resourceId][$component]->{$payload->method}(...$payload->params);
                break;
            default:
                throw new \Exception('Unrecongnized event: ' . $event);
                break;
        }

        $from->send(json_encode([
            'component' => $component,
            'dom' => $this->liveViewsByResourceId[$from->resourceId][$component]->render()->render(),
        ]));

    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        $this->output->info("Connection closed: ({$conn->resourceId})");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();

        $this->output->warn("Error: ({$e->getMessage()})");
    }
}
