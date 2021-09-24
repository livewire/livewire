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

    /** @test */
    public function trigger_downloads_from_livewire_component_with_headers()
    {
        $this->onlyRunOnChrome();

        $this->browse(function ($browser) {

            // Download with content-type header.
            Livewire::visit($browser, Component::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.livewire.hook('message.received', (message, component) => {
                            document.querySelector('[dusk=\"content-type\"]').value = message.response.effects.download.contentType;
                        })",
                    ]);
                })
                ->waitForLivewire()->click('@download-with-content-type-header')
                ->tap(function ($b) {
                    $this->assertEquals('text/html', $b->value('@content-type'));
                })
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download-target.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target.txt')
            );

            // Download with null content-type header.
            Livewire::visit($browser, Component::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.livewire.hook('message.received', (message, component) => {
                            document.querySelector('[dusk=\"content-type\"]').value = message.response.effects.download.contentType;
                        })",
                    ]);
                })
                ->waitForLivewire()->click('@download-with-null-content-type-header')
                ->tap(function ($b) {
                    $this->assertEquals(null, $b->value('@content-type'));
                })
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download-target.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target.txt')
            );

            /**
             * Download an untitled file with "invalid" content-type header.
             * It mimics this test: dusk="download-an-untitled-file-with-content-type-header"
             */
            Livewire::visit($browser, Component::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.livewire.hook('message.received', (message, component) => {
                        document.querySelector('[dusk=\"content-type\"]').value = message.response.effects.download.contentType;
                    })",
                    ]);
                })
                ->waitForLivewire()->click('@download-an-untitled-file-with-invalid-content-type-header')
                ->tap(function ($b) {
                    $this->assertEquals('foo', $b->value('@content-type'));
                })
                ->waitUsing(5, 75, function () {
                    // Normally it should have been __.html --But the content type is invalid...
                    return Storage::disk('dusk-downloads')->exists('__.txt');
                });

            // ...But the file is still readable.
            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('__.txt')
            );

            // Download an untitled file with content-type header.
            Livewire::visit($browser, Component::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.livewire.hook('message.received', (message, component) => {
                            document.querySelector('[dusk=\"content-type\"]').value = message.response.effects.download.contentType;
                        })",
                    ]);
                })
                ->waitForLivewire()->click('@download-an-untitled-file-with-content-type-header')
                ->tap(function ($b) {
                    $this->assertEquals('text/html', $b->value('@content-type'));
                })
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('__.html');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('__.html')
            );

            /**
             * Trigger download with a response return.
             */

            Livewire::visit($browser, Component::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.livewire.hook('message.received', (message, component) => {
                            document.querySelector('[dusk=\"content-type\"]').value = message.response.effects.download.contentType;
                        })",
                    ]);
                })
                ->waitForLivewire()->click('@download-from-response-with-content-type-header')
                ->tap(function ($b) {
                    $this->assertEquals('text/csv', $b->value('@content-type'));
                })
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download-target2.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target2.txt')
            );
        });
    }
    
    /** @test */
    public function trigger_downloads_from_event_listener()
    {
        $this->onlyRunOnChrome();

        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->click('@emit-download')
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download-target.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target.txt')
            );
        });
    }
}
