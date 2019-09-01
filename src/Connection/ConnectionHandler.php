<?php

namespace Livewire\Connection;

use Livewire\ResponsePayload;
use Livewire\Routing\Redirector;
use Livewire\ComponentSessionManager;
use Livewire\ComponentChecksumManager;
use Illuminate\Validation\ValidationException;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        $instance = ComponentHydrator::hydrate($payload['name'], $payload['id'], $payload['data'], $payload['checksum']);

        $instance->setPreviouslyRenderedChildren($payload['children']);
        $instance->hashPropertiesForDirtyDetection();

        $instance->hydrate();

        try {
            $this->interceptRedirects($instance, function () use ($payload, $instance) {
                foreach ($payload['actionQueue'] as $action) {
                    $this->processMessage($action['type'], $action['payload'], $instance);
                }
            });
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
        }

        $dom = $instance->output($errors ?? null);
        $data = ComponentHydrator::dehydrate($instance);
        $events = $instance->getEventsBeingListenedFor();
        $eventQueue = $instance->getEventQueue();

        $response = new ResponsePayload([
            'id' => $payload['id'],
            'dom' => $dom,
            'checksum' => (new ComponentChecksumManager)->generate($payload['name'], $payload['id'], $data),
            'dirtyInputs' => $instance->getDirtyProperties(),
            'children' => $instance->getRenderedChildren(),
            'eventQueue' => $eventQueue,
            'events' => $events,
            'data' => $data,
            'redirectTo' => $instance->redirectTo ?? false,
            'fromPrefetch' => $payload['fromPrefetch'] ?? false,
            'gc' => ComponentSessionManager::garbageCollect($payload['gc']),
        ]);

        if (empty($instance->redirectTo)) {
            session()->forget(session()->get('_flash.new'));
        }

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
}
