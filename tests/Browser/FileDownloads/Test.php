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
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->click('@download')
                // Wait for download to be triggered.
                ->pause(500);

            $this->assertTrue(
                Storage::disk('dusk-downloads')->exists('download-target.txt')
            );

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target.txt')
            );

            /**
             * Trigger download with a response return.
             */
            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->click('@download-from-response')
                // Wait for download to be triggered.
                ->pause(500);

            $this->assertTrue(
                Storage::disk('dusk-downloads')->exists('download-target2.txt')
            );

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target2.txt')
            );
        });
    }
}
