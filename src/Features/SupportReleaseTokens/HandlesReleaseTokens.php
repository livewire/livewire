<?php

namespace Livewire\Features\SupportReleaseTokens;

trait HandlesReleaseTokens
{
    // This token is stored client-side and sent along with each request to check
    // a users session to see if a new release has invalidated it. If there is
    // a mismatch it will throw an error and prompt for a browser refresh.
    public static function releaseToken(): string
    {
        return 'a';
    }
}
