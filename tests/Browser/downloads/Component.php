<?php

namespace Tests\Browser\Alpine;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $show = false;

    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function download()
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);
        return Storage::disk('dusk-tmp')->download('Component.php');
    }

    public function upperMe($subject)
    {
        return strtoupper($subject);
    }

    public function getCount()
    {
        return $this->count;
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
