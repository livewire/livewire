<?php

namespace Livewire\Connection;

use Illuminate\Validation\ValidationException;

abstract class ConnectionHandler
{
    public function handle($event, $data, $serialized)
    {
        $instance = decrypt($payload['serialized']);

        try {
            $this->processEvent($event, $instance, $data);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
        }

        return [
            'id' => $instance->id,
            'dom' => $instance->output($errors ?? null),
            'dirtyInputs' => $instance->dirtyInputs(),
            'serialized' => encrypt($instance),
        ];
    }

    public function processEvent($event, $instance, $data)
    {
        $instance->beforeUpdate();

        switch ($event) {
            case 'refresh':
                break;
            case 'syncInput':
                $instance->syncInput($data['name'], $data['value']);
                break;
            case 'fireMethod':
                $instance->{$data['method']}(...$data['params']);
                break;
            default:
                throw new \Exception('Unrecongnized event: ' . $event);
                break;
        }

        $instance->updated();
    }
}
