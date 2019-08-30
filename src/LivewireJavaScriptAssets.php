<?php

namespace Livewire;

class LivewireJavaScriptAssets
{
    public function unminified()
    {
        return $this->pretendResponseIsFile(__DIR__.'/../dist/livewire.js');
    }

    public function minified()
    {
        return $this->pretendResponseIsFile(__DIR__.'/../dist/livewire.min.js');
    }

    public function pretendResponseIsFile($file)
    {
        // These headers will enable browsers to cache this asset.
        return response()
            ->file($file, [
                'Content-Type' => 'application/javascript; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
            ]);
    }
}
