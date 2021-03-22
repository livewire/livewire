<?php

namespace Livewire\Connection;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;
use Livewire\Exceptions\DirectlyCallingLifecycleHooksNotAllowedException;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        $instance = app('livewire')->activate($payload['name'], $payload['id']);

        try {
            Livewire::hydrate($instance, $payload);

            $instance->hydrate();

            foreach ($payload['actionQueue'] as $action) {
                $this->processMessage($action['type'], $action['payload'], $instance);
            }
        } catch (ValidationException $e) {
            Livewire::dispatch('failed-validation', $e->validator);

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
            case 'callMethod':
                throw_if(
                    Str::is([
                        'mount',
                        'hydrate*',
                        'dehydrate*',
                        'updating*',
                        'updated*',
                    ], $data['method']),
                    new DirectlyCallingLifecycleHooksNotAllowedException($data['method'], $instance->getName())
                );

                $instance->callMethod($data['method'], $data['params']);
                break;
            case 'fireEvent':
                $instance->fireEvent($data['event'], $data['params']);
                break;
            default:
                break;
        }
    }
}
