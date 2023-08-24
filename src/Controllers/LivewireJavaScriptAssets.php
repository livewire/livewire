<?php

namespace Livewire\Controllers;

class LivewireJavaScriptAssets
{
    use CanPretendToBeAFile;

    public function source()
    {
        $path = __DIR__.'/../../dist/livewire.js';
        return $this->pretendResponseIsFile(file_get_contents($path), filemtime($path));
    }

    public function maps()
    {
        $path = __DIR__.'/../../dist/livewire.js.map';
        return $this->pretendResponseIsFile(file_get_contents($path), filemtime($path));
    }
}
