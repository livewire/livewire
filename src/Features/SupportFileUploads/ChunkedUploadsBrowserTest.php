<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Component;
use Livewire\Livewire;

class ChunkedUploadsBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            // Small chunks so the 1MB test fixture uploads as many chunks...
            config([
                'livewire.temporary_file_upload.chunk_size' => 65536,
                'livewire.temporary_file_upload.chunk_threshold' => 65536,
            ]);
        };
    }

    public function test_files_over_the_chunk_threshold_upload_in_chunks_and_reassemble_correctly()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $file;

            function mount()
            {
                Storage::disk('tmp-for-tests')->deleteDirectory('files');
            }

            function save()
            {
                $this->file->storeAs('files', 'assembled.jpg', 'tmp-for-tests');
            }

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="file" dusk="upload">

                <div wire:loading wire:target="file">uploading...</div>

                @if ($file)
                    <span dusk="filename">{{ $file->getClientOriginalName() }}</span>
                @endif

                <button wire:click="save" dusk="save">Save</button>
            </div>
            HTML; }
        })
        ->attach('@upload', __DIR__ . '/browser_test_image_big.jpg')
        ->waitFor('@filename')
        ->assertSeeIn('@filename', 'browser_test_image_big.jpg')
        ->waitForLivewire()
        ->click('@save')
        ->tap(function () {
            $this->assertEquals(
                hash_file('sha256', __DIR__ . '/browser_test_image_big.jpg'),
                hash('sha256', Storage::disk('tmp-for-tests')->get('files/assembled.jpg'))
            );
        });
    }
}
