<?php

namespace Livewire\Connection;

use Illuminate\Validation\ValidationException;
use Livewire\LivewireOutput;
use Livewire\TinyHtmlMinifier;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        $instance = ComponentHydrator::hydrate($payload['name'], $payload['data'], $payload['checksum']);

        $instance->setPreviouslyRenderedChildren($payload['children']);
        $instance->hashPropertiesForDirtyDetection();

        try {
            foreach ($payload['actionQueue'] as $action) {
                $this->processMessage($action['type'], $action['payload'], $instance);
            }
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
        }

        if ($instance->redirectTo) {
            return ['redirectTo' => $instance->redirectTo];
        }

        $dom = $instance->output($errors ?? null);
        $data = ComponentHydrator::dehydrate($instance);
        $listeningFor = $instance->getEventsBeingListenedFor();
        $eventQueue = $instance->getEventQueue();

        // This is here because VueJs strips whitespace and this prevents Vue from breaking Livewire.
        $minifier = new TinyHtmlMinifier(['collapse_whitespace' => true]);

        return new LivewireOutput([
            // The "id" is here only as a way of relating the request to the response in js, no other reason.
            'id' => $payload['id'],
            // @todo - this breaks svgs (because of self-closing tags)
            // 'dom' => $minifier->minify($dom),
            'dom' => $dom,
            'dirtyInputs' => $instance->getDirtyProperties(),
            'children' => $instance->getRenderedChildren(),
            'eventQueue' => $eventQueue,
            'listeningFor' => $listeningFor,
            'checksum' => $payload['checksum'],
            'data' => $data,
        ]);
    }

    public function processMessage($type, $data, $instance)
    {
        $instance->updating();

        switch ($type) {
            case 'syncInput':
                $instance->syncInput($data['name'], $data['value']);
                break;
            case 'callMethod':
                $instance->callMethod($data['method'], $data['params']);
                break;
            case 'fireEvent':
                $instance->fireEvent($data['event'], $data['params']);
                break;
            default:
                throw new \Exception('Unrecongnized message type: ' . $type);
                break;
        }

        $instance->updated();
    }
}
