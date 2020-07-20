<?php

namespace Tests\Browser\FileDownloads;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function download()
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return Storage::disk('dusk-tmp')->download('download-target.txt');
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
