<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Livewire;
use Illuminate\Validation\ValidationException;

class PerformActionCalls implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        try {
            foreach ($request->updates as $update) {
                if ($update['type'] !== 'callMethod') continue;

                $method = $update['payload']['method'];
                $params = $update['payload']['params'];

                $unHydratedInstance->callMethod($method, $params);
            }
        } catch (ValidationException $e) {
            Livewire::dispatch('failed-validation', $e->validator);

            $unHydratedInstance->setErrorBag($e->validator->errors());
        }
    }

    public static function dehydrate($instance, $response)
    {
        //
    }
}
