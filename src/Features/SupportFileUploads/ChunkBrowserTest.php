<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Component;
use Livewire\Livewire;

class ChunkBrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_upload_file_with_chunked_modifier()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function mount()
            {
                Storage::disk('tmp-for-tests')->deleteDirectory('photos');
            }

            function save()
            {
                $this->photo->storeAs('photos', 'photo.png', 'tmp-for-tests');
            }

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model.chunked.1="photo" dusk="upload">

                <div>
                    @if ($photo)
                        <span dusk="preview">uploaded</span>
                    @endif
                </div>

                <button wire:click="save" dusk="save">Save</button>
            </div>
            HTML; }
        })
        ->assertMissing('@preview')
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->waitFor('@preview')
        ->assertVisible('@preview')
        ->tap(function () {
            Storage::disk('tmp-for-tests')->assertMissing('photos/photo.png');
        })
        ->waitForLivewire()
        ->click('@save')
        ->tap(function () {
            Storage::disk('tmp-for-tests')->assertExists('photos/photo.png');
        })
        ;
    }

    public function test_chunked_upload_fires_progress_events()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model.chunked.1="photo" dusk="upload">

                <span dusk="progress" x-data="{ pct: 0 }"
                    x-on:livewire-upload-progress.window="pct = $event.detail.progress"
                    x-text="pct"></span>

                <div>
                    @if ($photo)
                        <span dusk="done">done</span>
                    @endif
                </div>
            </div>
            HTML; }
        })
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->waitFor('@done')
        ->assertSeeIn('@progress', '100')
        ;
    }

    public function test_can_cancel_a_chunked_upload()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model.chunked.1="photo" dusk="upload">

                <button wire:click="$cancelUpload('photo')" dusk="cancel">cancel</button>

                <span dusk="status" x-data="{ status: 'idle' }"
                    x-on:livewire-upload-cancel.window="status = 'cancelled'"
                    x-on:livewire-upload-finish.window="status = 'finished'"
                    x-text="status"></span>
            </div>
            HTML; }
        })
        ->attach('@upload', __DIR__ . '/browser_test_image_big.jpg')
        ->pause(50)
        ->click('@cancel')
        ->waitForTextIn('@status', 'cancelled')
        ->assertSeeIn('@status', 'cancelled')
        ;
    }
}
