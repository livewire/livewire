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

        $checksum = $request->memo['checksum'];

        unset($request->memo['checksum']);

        throw_unless(
            $checksumManager->check($checksum, $request->fingerprint, $request->memo),
            new CorruptComponentPayloadException($unHydratedInstance::getName())
        );
    }

    public static function dehydrate($instance, $response)
    {
        $response->memo['checksum'] = (new ComponentChecksumManager)->generate($response->fingerprint, $response->memo);
    }
}
