<?php

namespace Livewire\Connection;

use Livewire\Request;
use Livewire\Livewire;
use Livewire\Response;
use Illuminate\Validation\ValidationException;

abstract class ConnectionHandler
{
    public function handle($payload)
    {
        $request = new Request($payload);

        $instance = app('livewire')->activate($request->name(), $request->id());

        try {
            Livewire::hydrate($instance, $request);

            $instance->hydrate();

            foreach ($request->updates as $update) {
                $this->processMessage($update['type'], $update['payload'], $instance);
            }
        } catch (ValidationException $e) {
            Livewire::dispatch('failed-validation', $e->validator);

            $errors = $e->validator->errors();
        }

        $html = $instance->output($errors ?? null);

        $response = Response::fromRequest($request, $html);

        Livewire::dehydrate($instance, $response);

        return $response->toSusequentLaravelResponse();
    }

    public function processMessage($type, $data, $instance)
    {
        switch ($type) {
            case 'callMethod':
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
