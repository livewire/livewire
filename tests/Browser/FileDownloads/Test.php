<?php

namespace Tests\Browser\FileDownloads;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Illuminate\Support\Facades\Storage;

class Test extends TestCase
{
    /** @test */
    public function trigger_downloads_from_livewire_component()
    {
        $this->onlyRunOnChrome();

        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->click('@download')
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download-target.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target.txt')
            );

            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->click('@download-quoted-disposition-filename')
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download & target.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download & target.txt')
            );

            /**
             * Trigger download with a response return.
             */
            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->click('@download-from-response')
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download-target2.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target2.txt')
            );

            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->click('@download-from-response-quoted-disposition-filename')
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download & target2.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download & target2.txt')
            );
        });
    }
}
