<?php

namespace Livewire\Features\SupportReleaseTokens;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportReleaseTokens extends ComponentHook
{
    public static function provide()
    {
        on('dehydrate', function ($component, $context) {
            $context->addMemo('release', ReleaseToken::generate($component));
        });

        // Use `snapshot-verified` to run the check before any component properties are hydrated
        // but after the snapshot has been verified to ensure it hasn't been tampered with...
        on('snapshot-verified', function ($snapshot) {
            ReleaseToken::verify($snapshot);
        });
    }
}
