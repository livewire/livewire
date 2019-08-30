<?php

namespace Livewire;

class LivewireJavaScriptAssets
{
    public function __invoke()
    {
        $file = __DIR__.'/../dist/livewire.js';

        // These headers will enable browsers to cache this asset.
        return response()
            ->file($file, [
                'Content-Type' => 'application/javascript; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
            ]);
    }
}
