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

        // Verify the release token before verifying the snapshot checksum, as we don't want to trigger a checksum
        // failure if the release token doesn't match as there may be intentional changes in the snapshot...
        on('checksum.verify', function ($checksum, $snapshot) {
            ReleaseToken::verify($snapshot);
        });
    }
}
