<?php

namespace Livewire\Connection;

use Illuminate\Validation\ValidationException;

abstract class ConnectionHandler
{
    public function handle($event, $data, $serialized)
    {
        app('livewire')->isRunningOnPageLoad = false;

        $instance = decrypt($serialized);

        try {
            $this->processEvent($event, $instance, $data);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
        }

        $id = $instance->id;
        if ($instance->redirectTo) {
            return ['redirectTo' => $instance->redirectTo];
        }
        $dom = $instance->output($errors ?? null);
        $dirtyInputs = $instance->dirtyInputs();
        $callOnParent = $instance->callOnParent;
        $serialized = encrypt($instance);

        return [
            'id' => $id,
            'dom' => app('livewire')->wrap($dom, $id, $serialized),
            'dirtyInputs' => $dirtyInputs,
            'serialized' => $serialized,
            'ref' => $data['ref'] ?? null,
            'callOnParent' => $callOnParent,
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
