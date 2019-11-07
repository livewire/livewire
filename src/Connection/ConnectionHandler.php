<?php

namespace Livewire\Connection;

use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Livewire\SubsequentResponsePayload;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        $class = app('livewire')->getComponentClass($payload['name']);

        $instance = new $class($payload['id']);

        Livewire::hydrate($instance, $payload);

        $instance->hydrate();

        try {
            foreach ($payload['actionQueue'] as $action) {
                $this->processMessage($action['type'], $action['payload'], $instance);
            }
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
        }

        $dom = $instance->output($errors ?? null);

        $events = $instance->getEventsBeingListenedFor();
        $eventQueue = $instance->getEventQueue();

        $response = new SubsequentResponsePayload([
            'id' => $payload['id'],
            'name' => $payload['name'],
            'dom' => $dom,
            'eventQueue' => $eventQueue,
            'events' => $events,
            'redirectTo' => $instance->redirectTo ?? false,
            'fromPrefetch' => $payload['fromPrefetch'] ?? false,
        ]);

        Livewire::dehydrate($instance, $response);

        return $response;
    }

    public function processMessage($type, $data, $instance)
    {
        switch ($type) {
            case 'syncInput':
                $instance->updating($data['name'], $data['value']);
                $instance->syncInput($data['name'], $data['value']);
                $instance->updated($data['name'], $data['value']);
                break;
            case 'callMethod':
                $instance->callMethod($data['method'], $data['params']);
                break;
            case 'fireEvent':
                $instance->fireEvent($data['event'], $data['params']);
                break;
            default:
                throw new \Exception('Unrecongnized message type: '.$type);
                break;
        }
    }
}
