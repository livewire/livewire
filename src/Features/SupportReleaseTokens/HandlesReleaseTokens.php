<?php

namespace Livewire\Features\SupportReleaseTokens;

trait HandlesReleaseTokens
{
    // This is a per-component release token that developers can use to indicate if
    // a component has had any breaking changes. When you change this token, any
    // instances of this component that are running in the browser will get a
    // 419 response prompting users to refresh the page...
    public static function releaseToken(): string
    {
        return 'a';
    }
}
