<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Livewire;
use Illuminate\Validation\ValidationException;

class PerformDataBindingUpdates implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        try {
            foreach ($request->updates as $update) {
                if ($update['type'] !== 'syncInput') continue;

                $data = $update['payload'];

                $unHydratedInstance->syncInput($data['name'], $data['value']);
            }
        } catch (ValidationException $e) {
            Livewire::dispatch('failed-validation', $e->validator, $unHydratedInstance);

            $unHydratedInstance->setErrorBag($e->validator->errors());
        }
    }

    public static function dehydrate($instance, $response)
    {
        //
    }
}
