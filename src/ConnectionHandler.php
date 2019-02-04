<?php

namespace Livewire;

use Illuminate\Validation\ValidationException;

abstract class ConnectionHandler
{
    public function handle($payload, $instance)
    {
        $event = $payload['event'] ?? 'init';
        $component = $payload['component'];
        $payload = $payload['payload'];

        try {
            switch ($event) {
                case 'init':
                    $instance->mounted();
                    break;
                case 'form-input':
                    $instance->formInput($payload['form'], $payload['input'], $payload['value']);
                    break;
                case 'sync':
                    $instance->sync($payload['model'], $payload['value']);
                    // // If we don't return early we cost too much in rendering AND break input elements for some reason.
                    // return;
                    break;
                case 'fireMethod':
                    $instance->{$payload['method']}(...$payload['params']);
                    break;
                default:
                    throw new \Exception('Unrecongnized event: ' . $event);
                    break;
            }
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
        }

        $dom = $instance->view($errors ?? null)->render();
        $refreshForms = $instance->formsThatNeedInputRefreshing();
        $instance->clearFormRefreshes();

        return [
            'component' => $component,
            'refreshForms' => $refreshForms,
            'dom' => $dom,
        ];

        switch ($event) {
            case 'init':
                $instance->mounted();
                break;
            case 'sync':
                $instance->sync($payload->model, $payload->value);
                break;
            case 'fireMethod':
                $instance->{$payload->method}(...$payload->params);
                break;
            default:
                throw new \Exception('Unrecongnized event: ' . $event);
                break;
        }
    }
}
