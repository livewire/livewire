<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_upload_preview_and_save_a_file()
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
                $this->photo->storeAs('photos', 'photo.png');
            }

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="photo" dusk="upload">

                <div wire:loading wire:target="photo">uploading...</div>

                <button wire:click="$refresh">refresh</button>

                <div>
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" dusk="preview">
                    @endif
                </div>

                <button wire:click="save" dusk="save">Save</button>
            </div>
            HTML; }
        })
        ->assertMissing('@preview')
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->pause(250)
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

    /** @test */
    public function can_use_method_name_upload()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function mount()
            {
                Storage::disk('tmp-for-tests')->deleteDirectory('photos');
            }

            function upload()
            {
                $this->photo->storeAs('photos', 'photo.png');
            }

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="photo" dusk="upload">

                <div wire:loading wire:target="photo">uploading...</div>

                <button wire:click="$refresh">refresh</button>

                <div>
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" dusk="preview">
                    @endif
                </div>

                <button wire:click="upload" dusk="save">Save</button>
            </div>
            HTML; }
        })
            ->assertMissing('@preview')
            ->attach('@upload', __DIR__ . '/browser_test_image.png')
            ->pause(250)
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
}
