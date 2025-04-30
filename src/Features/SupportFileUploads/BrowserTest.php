<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Component;
use Livewire\Features\SupportValidation\BaseValidate;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_upload_preview_and_save_a_file()
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

    public function test_can_cancel_an_upload()
    {
        if (getenv('FORCE_RUN') !== '1') {
            $this->markTestSkipped('Skipped');
        }

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
            <div x-on:livewire-upload-cancel="$el.querySelector('h1').textContent = 'cancelled'">
                <input type="file" wire:model="photo" dusk="upload">

                <div wire:loading wire:target="photo">uploading...</div>

                <button wire:click="$cancelUpload('photo')" dusk="cancel">cancel</button>

                <h1 dusk="output"></h1>

                <button wire:click="save" dusk="save">Save</button>
            </div>
            HTML; }
        })
        ->assertMissing('@preview')
        ->attach('@upload', __DIR__ . '/browser_test_image_big.jpg')
        ->pause(5)
        ->click('@cancel')
        ->assertSeeIn('@output', 'cancelled')
        ;
    }

    public function test_an_element_targeting_a_file_upload_retains_loading_state_until_the_upload_has_finished()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component
        {
            use \Livewire\WithFileUploads;

            public $photo;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <input type="file" wire:model="photo" dusk="upload" />

                        <p wire:loading wire:target="photo" id="loading" dusk="loading">Loading</p>
                    </div>
                HTML;
            }
        })
        ->waitForLivewireToLoad()
        // Set a script to record if the loading element was displayed when `livewire-upload-progress` was fired
        ->tap(fn ($b) => $b->script([
            "window.Livewire.first().on('livewire-upload-progress', () => { window.loadingWasDisplayed = document.getElementById('loading').style.display === 'inline-block' })",
        ]))
        ->assertMissing('@loading')

        ->waitForLivewire()->attach('@upload', __DIR__.'/browser_test_image_big.jpg')

        // Wait for Upload to finish
        ->waitUntilMissing('@loading')
        // Assert that the loading element was displayed while `livewire-upload-progress` was emitted
        ->assertScript('window.loadingWasDisplayed', true)
        ;
    }

    public function test_file_upload_being_renderless_is_not_impacted_by_real_time_validation()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component
        {
            use \Livewire\WithFileUploads;

            #[BaseValidate(['required', 'min:3'])]
            public $foo;

            public $photo;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <input type="text" wire:model="foo" dusk="foo" />

                        <div>
                            @error('foo')
                                <span dusk="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <input type="file" wire:model="photo" dusk="upload" />

                        <div>
                            @if ($photo)
                                Preview
                                <img src="{{ $photo->temporaryUrl() }}" dusk="preview">
                            @endif
                        </div>
                    </div>
                HTML;
            }
        })
        ->assertNotPresent('@preview')
        ->assertNotPresent('@error')

        ->type('@foo', 'ba')

        ->waitForLivewire()->attach('@upload', __DIR__.'/browser_test_image_big.jpg')

        ->waitFor('@preview')->assertVisible('@preview')
        ->assertVisible('@error')
        ;
    }

    public function test_can_clear_out_file_input_after_property_has_been_reset()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function mount()
            {
                Storage::disk('tmp-for-tests')->deleteDirectory('photos');
            }

            function resetFileInput()
            {
                unset($this->photo);
            }

            function render() { return <<<'HTML'
                <div>
                    <input type="file" wire:model="photo" dusk="upload">

                    <button wire:click="resetFileInput" dusk="resetFileInput">ResetFileInput</button>
                </div>
                HTML;
            }
        })
        ->assertInputValue('@upload', null)
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        // Browsers will return the `C:\fakepath\` prefix for security reasons
        ->assertInputValue('@upload', 'C:\fakepath\browser_test_image.png')
        ->pause(250)
        ->waitForLivewire()
        ->click('@resetFileInput')
        ->assertInputValue('@upload', null)
        ;
    }

    public function test_can_clear_out_multiple_file_input_after_property_has_been_reset()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function mount()
            {
                Storage::disk('tmp-for-tests')->deleteDirectory('photos');
            }

            function resetFileInput()
            {
                $this->photos = [];
            }

            function render() { return <<<'HTML'
                <div>
                    <input type="file" wire:model="photos" dusk="upload" multiple>

                    <button wire:click="resetFileInput" dusk="resetFileInput">ResetFileInput</button>
                </div>
                HTML;
            }
        })
        ->assertInputValue('@upload', null)
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->attach('@upload', __DIR__ . '/browser_test_image2.png')
        // Browsers will return the `C:\fakepath\` prefix for security reasons
        // The first file input should have the first file as the value, but it will display '2 files' in the label
        ->assertInputValue('@upload', 'C:\fakepath\browser_test_image.png')
        ->pause(250)
        ->waitForLivewire()
        ->click('@resetFileInput')
        ->assertInputValue('@upload', null)
        ;
    }
}
