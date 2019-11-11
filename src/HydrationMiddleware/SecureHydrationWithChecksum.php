<?php

namespace Livewire\HydrationMiddleware;

use Livewire\ComponentChecksumManager;
use Livewire\Exceptions\CorruptComponentPayloadException;

class SecureHydrationWithChecksum implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        // Make sure the data coming back to hydrate a component hasn't been tampered with.
        $checksumManager = new ComponentChecksumManager;

        throw_unless(
            $checksumManager->check($request['checksum'], $request['name'], $request['id'], $request['data']),
            new CorruptComponentPayloadException($request['name'])
        );
    }

    public static function dehydrate($instance, $response)
    {
        $response->checksum = (new ComponentChecksumManager)->generate($response->name, $response->id, $response->data);
    }
}
