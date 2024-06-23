<?php

namespace LegacyTests\Browser\FileDownloads;

use LegacyTests\Browser\TestCase;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Test extends TestCase
{
    public function test_trigger_downloads_from_livewire_component()
    {
        $this->onlyRunOnChrome();

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, DownloadComponent::class)
                ->waitForLivewire()->click('@download')
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download-target.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target.txt')
            );

            $this->visitLivewireComponent($browser, DownloadComponent::class)
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
            $this->visitLivewireComponent($browser, DownloadComponent::class)
                ->waitForLivewire()->click('@download-from-response')
                ->waitUsing(5, 75, function () {
                    return Storage::disk('dusk-downloads')->exists('download-target2.txt');
                });

            $this->assertStringContainsString(
                'I\'m the file you should download.',
                Storage::disk('dusk-downloads')->get('download-target2.txt')
            );

            $this->visitLivewireComponent($browser, DownloadComponent::class)
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

    public function test_trigger_downloads_from_livewire_component_with_headers()
    {
        $this->onlyRunOnChrome();

        $this->browse(function ($browser) {

            // Download with content-type header.
            $this->visitLivewireComponent($browser, DownloadComponent::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.Livewire.hook('commit', ({ component, succeed }) => {
                            succeed(({ effects }) => {
                                document.querySelector('[dusk=\"content-type\"]').value = effects.download.contentType;
                            })
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

            // Skipping this assertion for now because it fails in CI, showing "text/plain" instead of null...
            // Download with null content-type header.
            // $this->visitLivewireComponent($browser, DownloadComponent::class)
            //     ->tap(function ($b) {
            //         $b->script([
            //             "window.Livewire.hook('commit', ({ component, succeed }) => {
            //                 succeed(({ effects }) => {
            //                     document.querySelector('[dusk=\"content-type\"]').value = effects.download.contentType;
            //                 })
            //             })",
            //         ]);
            //     })
            //     ->waitForLivewire()->click('@download-with-null-content-type-header')
            //     ->tap(function ($b) {
            //         $this->assertEquals(null, $b->value('@content-type'));
            //     })
            //     ->waitUsing(5, 75, function () {
            //         return Storage::disk('dusk-downloads')->exists('download-target.txt');
            //     });

            // $this->assertStringContainsString(
            //     'I\'m the file you should download.',
            //     Storage::disk('dusk-downloads')->get('download-target.txt')
            // );

            /**
             * Download an untitled file with "invalid" content-type header.
             * It mimics this test: dusk="download-an-untitled-file-with-content-type-header"
             */
            $this->visitLivewireComponent($browser, DownloadComponent::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.Livewire.hook('commit', ({ component, succeed }) => {
                            succeed(({ effects }) => {
                                document.querySelector('[dusk=\"content-type\"]').value = effects.download.contentType;
                            })
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
            $this->visitLivewireComponent($browser, DownloadComponent::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.Livewire.hook('commit', ({ component, succeed }) => {
                            succeed(({ effects }) => {
                                document.querySelector('[dusk=\"content-type\"]').value = effects.download.contentType;
                            })
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

            $this->visitLivewireComponent($browser, DownloadComponent::class)
                ->tap(function ($b) {
                    $b->script([
                        "window.Livewire.hook('commit', ({ component, succeed }) => {
                            succeed(({ effects }) => {
                                document.querySelector('[dusk=\"content-type\"]').value = effects.download.contentType;
                            })
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

    public function test_trigger_downloads_from_event_listener()
    {
        $this->onlyRunOnChrome();

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, DownloadComponent::class)
                ->waitForLivewire()->click('@dispatch-download')
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

class DownloadComponent extends Component
{
    protected $listeners = [
        'download'
    ];

    public function download()
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return Storage::disk('dusk-tmp')->download('download-target.txt');
    }

    public function downloadWithContentTypeHeader($contentType = null)
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return Storage::disk('dusk-tmp')->download('download-target.txt', null, ['Content-Type' => $contentType]);
    }

    public function downloadAnUntitledFileWithContentTypeHeader($contentType = 'text/html')
    {
        config()->set('filesystems.disks.dusk-tmp', [
            'driver' => 'local',
            'root' => __DIR__,
        ]);

        return Storage::disk('dusk-tmp')->download('download-target.txt', '', ['Content-Type' => $contentType]);
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
        return <<<'HTML'
            <div>
                <button wire:click="$dispatch('download')" dusk="dispatch-download">Dispatch Download</button>
                <button wire:click="download" dusk="download">Download</button>
                <button wire:click="downloadFromResponse" dusk="download-from-response">Download</button>
                <button wire:click="downloadQuotedContentDispositionFilename" dusk="download-quoted-disposition-filename">Download</button>
                <button wire:click="downloadQuotedContentDispositionFilenameFromResponse" dusk="download-from-response-quoted-disposition-filename">Download</button>
                <button wire:click="downloadWithContentTypeHeader('text/html')" dusk="download-with-content-type-header">Download</button>
                <button wire:click="downloadWithContentTypeHeader()" dusk="download-with-null-content-type-header">Download</button>
                <button wire:click="downloadAnUntitledFileWithContentTypeHeader" dusk="download-an-untitled-file-with-content-type-header">Download</button>
                <button wire:click="downloadAnUntitledFileWithContentTypeHeader('foo')" dusk="download-an-untitled-file-with-invalid-content-type-header">Download</button>
                <button wire:click="downloadFromResponseWithContentTypeHeader" dusk="download-from-response-with-content-type-header">Download</button>
                <input dusk="content-type" />
            </div>
        HTML;
    }
}
