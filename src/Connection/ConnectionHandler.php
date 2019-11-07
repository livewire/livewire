<?php

namespace Livewire\Connection;

use Illuminate\Validation\ValidationException;
use Livewire\ComponentCacheManager;
use Livewire\Livewire;
use Livewire\Routing\Redirector;
use Livewire\SubsequentResponsePayload;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        $class = app('livewire')->getComponentClass($payload['name']);

        $instance = new $class($payload['id']);

        Livewire::hydrate($instance, $payload);

        $instance->setPreviouslyRenderedChildren($payload['children']);
        $instance->hashPropertiesForDirtyDetection();

        $instance->hydrate();

        try {
            $this->interceptRedirects($instance, function () use ($payload, $instance) {
                foreach ($this->prioritizeInputSyncing($payload['actionQueue']) as $action) {
                    $this->processMessage($action['type'], $action['payload'], $instance);
                }
            });
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
            'dirtyInputs' => $instance->getDirtyProperties(),
            'children' => $instance->getRenderedChildren(),
            'eventQueue' => $eventQueue,
            'events' => $events,
            'redirectTo' => $instance->redirectTo ?? false,
            'fromPrefetch' => $payload['fromPrefetch'] ?? false,
            'gc' => ComponentCacheManager::garbageCollect($payload['gc']),
        ]);

        if (empty($instance->redirectTo)) {
            session()->forget(session()->get('_flash.new'));
        }

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

    protected function interceptRedirects($instance, $callback)
    {
        $redirector = app('redirect');

        app()->bind('redirect', function () use ($instance) {
            return app(Redirector::class)->component($instance);
        });

        $callback();

        app()->instance('redirect', $redirector);
    }

    protected function prioritizeInputSyncing($actionQueue)
    {
        // Put all the "syncInput" actions first.
        usort($actionQueue, function ($a, $b) {
            return $a['type'] !== 'syncInput' && $b['type'] === 'syncInput'
                ? 1 : 0;
        });

        return $actionQueue;
    }
}
