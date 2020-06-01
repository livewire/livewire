<?php

namespace Livewire\Controllers;

class LivewireJavaScriptAssets
{
    use CanPretendToBeAFile;

    public function source()
    {
        return $this->pretendResponseIsFile(__DIR__.'/../../dist/livewire.js');
    }

    public function maps()
    {
        return $this->pretendResponseIsFile(__DIR__.'/../../dist/livewire.js.map');
    }
}
