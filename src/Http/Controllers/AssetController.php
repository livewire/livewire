<?php

namespace Livewire\Http\Controllers;

class AssetController
{
    public function __invoke()
    {
        $file = __DIR__ . '/../../../dist/livewire.js';
        $lastModified = filemtime($file);
        $contents = file_get_contents($file);

        // These headers will enable browsers to cache this asset.
        return response($contents)
            ->withHeaders([
                'Content-Type' => 'application/javascript; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
                'Last-Modified' => gmdate("D, d M Y H:i:s", $lastModified) . " GMT",
            ]);
    }
}
