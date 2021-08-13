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

    public function downloadWithContentTypeHeader()
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return Storage::disk('dusk-tmp')->download('download-target.txt', null, ['Content-Type' => 'text/html']);
    }

    public function downloadAnUntitledFileWithContentTypeHeader()
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return Storage::disk('dusk-tmp')->download('download-target.txt', '', ['Content-Type' => 'text/html']);
    }

    public function downloadFromResponse()
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return response()->download(
            Storage::disk('dusk-tmp')->path('download-target2.txt')
        );
    }

    public function downloadFromResponseWithContentTypeHeader()
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return response()->download(
            Storage::disk('dusk-tmp')->path('download-target2.txt'),
            'download-target2.txt',
            ['Content-Type' => 'text/csv']
        );
    }

    public function downloadQuotedContentDispositionFilename()
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return Storage::disk('dusk-tmp')->download('download & target.txt');
    }

    public function downloadQuotedContentDispositionFilenameFromResponse()
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return response()->download(
            Storage::disk('dusk-tmp')->path('download & target2.txt')
        );
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
