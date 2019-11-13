<?php

namespace Livewire\Connection;

use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        $instance = app('livewire')->activate($payload['name'], $payload['id']);

        Livewire::hydrate($instance, $payload);

        $instance->hydrate();

        try {
            foreach ($payload['actionQueue'] as $action) {
                $this->processMessage($action['type'], $action['payload'], $instance);
            }
        } catch (ValidationException $e) {
            $this->interceptValidator($e->validator);

            $errors = $e->validator->errors();
        }

        $dom = $instance->output($errors ?? null);

        $response = new Fluent([
            'id' => $payload['id'],
            'name' => $payload['name'],
            'dom' => $dom,
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

    public function interceptValidator($validator)
    {
        //
    }
}
