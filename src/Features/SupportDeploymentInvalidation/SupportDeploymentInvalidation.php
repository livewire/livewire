<?php

namespace Livewire\Features\SupportDeploymentInvalidation;

use Livewire\ComponentHook;
use Livewire\Exceptions\LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges;

use function Livewire\on;

class SupportDeploymentInvalidation extends ComponentHook
{
    public static $LIVEWIRE_DEPLOYMENT_INVALIDATION_HASH = 'acj';
    
    public static function provide()
    {
        // Use `snapshot-verified` to run the check before any component properties are hydrated 
        // but after the snapshot has been verified to ensure it hasn't been tampered with...
        on('snapshot-verified', function ($snapshot) {
            // If the `invalid` hash isn't in the memo, the component was likely mounted before the
            // deployment invalidation hash was added so we don't want to throw an error here...
            if (! isset($snapshot['memo']['invalid'])) return;

            if ($snapshot['memo']['invalid'] !== static::$LIVEWIRE_DEPLOYMENT_INVALIDATION_HASH) {
                throw new LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges;
            }
        });

        on('dehydrate', function ($component, $context) {
            $context->addMemo('invalid', static::$LIVEWIRE_DEPLOYMENT_INVALIDATION_HASH);
        });
    }
}
