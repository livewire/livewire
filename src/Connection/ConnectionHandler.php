<?php

namespace Livewire\Connection;

use Illuminate\Validation\ValidationException;
use Livewire\LivewireComponentWrapper;

abstract class ConnectionHandler
{
    public function wrap($instance)
    {
        return LivewireComponentWrapper::wrap($instance);
    }

    public function handle($actionQueue, $syncQueue, $serialized)
    {
        $instance = decrypt($serialized);
        $wrapped = $this->wrap($instance);

        try {
            foreach ($syncQueue ?? [] as $model => $value) {
                $wrapped->lazySyncInput($model, $value);
            }

            foreach ($actionQueue as $action) {
                $this->processMessage($action['type'], $action['payload'], $wrapped);
            }
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
        }

        if ($instance->redirectTo) {
            return ['redirectTo' => $instance->redirectTo];
        }

        $id = $instance->id;
        $dom = $wrapped->output($errors ?? null);
        $dirtyInputs = $wrapped->dirtyInputs();
        $emitEvent = $instance->emitEvent;
        $forQueryString = $instance->forQueryString;
        $instance->forQueryString = [];
        $serialized = encrypt($instance);

        return [
            'componentId' => $id,
            'dom' => app('livewire')->injectComponentDataAsHtmlAttributesInRootElement(
                $dom, $id, $serialized
            ),
            'dirtyInputs' => $dirtyInputs,
            'serialized' => $serialized,
            'emitEvent' => $emitEvent,
            'forQueryString' => $forQueryString,
        ];
    }

    public function processMessage($type, $data, $wrapped)
    {
        $wrapped->beforeUpdate();

        switch ($type) {
            case 'refresh':
                break;
            case 'syncInput':
                $wrapped->syncInput($data['name'], $data['value']);
                break;
            case 'fireEvent':
                $wrapped->fireEvent($data['childId'], $data['name'], $data['params']);
                break;
            case 'callMethod':
                $wrapped->callMethod($data['method'], $data['params']);
                break;
            default:
                throw new \Exception('Unrecongnized message type: ' . $type);
                break;
        }

        $wrapped->updated();
    }
}
