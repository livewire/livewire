<?php

namespace Livewire\Features\SupportReleaseTokens;

use Livewire\Exceptions\LivewireReleaseTokenMismatchException;
use Livewire\Mechanisms\ComponentRegistry;

class ReleaseToken {
    // This is the release token used by Livewire to verify that components running in the browser
    // are compatible with the internal serverside implementation. Maintainers will update this
    // token when deploying changes that would invalidate currently running components...
    public static $LIVEWIRE_RELEASE_TOKEN = 'a';

    static function verify($snapshot): void
    {
        $componentClass = app(ComponentRegistry::class)->getClass($snapshot['memo']['name']);

        if (!isset($snapshot['memo']['release']) || $snapshot['memo']['release'] !== static::generate($componentClass)) {
            throw new LivewireReleaseTokenMismatchException;
        }
    }

    static function generate($componentOrComponentClass): string
    {
        $livewireReleaseToken = static::$LIVEWIRE_RELEASE_TOKEN;
        $appReleaseToken = app('config')->get('livewire.release_token', '');
        $componentReleaseToken = method_exists($componentOrComponentClass, 'releaseToken') ? $componentOrComponentClass::releaseToken() : '';

        return $livewireReleaseToken . '-' . $appReleaseToken . '-' . $componentReleaseToken;
    }
}