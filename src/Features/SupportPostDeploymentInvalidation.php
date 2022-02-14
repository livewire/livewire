<?php

namespace Livewire\Features;

use Exception;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Livewire\Exceptions\LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges;

class SupportPostDeploymentInvalidation
{
    public static $LIVEWIRE_DEPLOYMENT_INVALIDATION_HASH = 'acj';

    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.dehydrate.initial', function ($component, $response) {
            $response->fingerprint['v'] = static::$LIVEWIRE_DEPLOYMENT_INVALIDATION_HASH;
        });

        Livewire::listen('component.hydrate.subsequent', function ($component, $request) {
            if (! isset($request->fingerprint['v'])) return;

            if ($v = $request->fingerprint['v']) {
                if ($v != static::$LIVEWIRE_DEPLOYMENT_INVALIDATION_HASH) {
                    throw new LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges;
                }
            }
        });
    }
}
