<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\WithFileUploads;
use Livewire\Component;
use Livewire\Facades\GenerateSignedUploadUrlFacade;
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
                $this->photo->storeAs('photos', 'photo.png', 'tmp-for-tests');
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
                $this->photo->storeAs('photos', 'photo.png', 'tmp-for-tests');
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
        ->waitUntil("document.querySelector('[dusk=\"upload\"]')?.value === ''")
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
        ->waitUntil("document.querySelector('[dusk=\"upload\"]')?.value === ''")
        ->assertInputValue('@upload', null)
        ;
    }

    public function test_removing_a_conditionally_rendered_file_input_does_not_accumulate_component_cleanups()
    {
        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            public $showUpload = false;

            function render() { return <<<'HTML'
                <div>
                    <button wire:click="$toggle('showUpload')" dusk="toggle">Toggle</button>

                    @if ($showUpload)
                        <input type="file" wire:model="photo" dusk="upload">
                    @endif
                </div>
                HTML;
            }
        })
        // One full mount/teardown cycle so any one-time registrations have happened...
        ->waitForLivewire()->click('@toggle')
        ->waitForLivewire()->click('@toggle')
        ->tap(function ($b) use (&$cleanupsAfterFirstCycle) {
            $cleanupsAfterFirstCycle = $b->script('return window.Livewire.all()[0].cleanups.length')[0];
        })
        ->waitForLivewire()->click('@toggle')
        ->waitForLivewire()->click('@toggle')
        ->waitForLivewire()->click('@toggle')
        ->waitForLivewire()->click('@toggle')
        ->tap(function ($b) use (&$cleanupsAfterFirstCycle) {
            $cleanupsAfterThreeMoreCycles = $b->script('return window.Livewire.all()[0].cleanups.length')[0];

            // Before the fix, every mount/teardown of the file input left a component-lifetime
            // cleanup behind (its $watch closure), stranding the input's detached DOM subtree...
            $this->assertSame(
                $cleanupsAfterFirstCycle,
                $cleanupsAfterThreeMoreCycles,
                'Tearing down a wire:model file input leaked a component-lifetime cleanup per cycle',
            );
        })
        ;
    }

    public function test_finish_upload_rejects_forged_unsigned_paths()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;
            public $result = 'pending';

            function mount()
            {
                Storage::disk('tmp-for-tests')->deleteDirectory('photos');
            }

            function render() { return <<<'HTML'
                <div>
                    <input type="file" wire:model="photo" dusk="upload">

                    <button
                        dusk="forge"
                        x-on:click="
                            $wire.call('_finishUpload', 'photo', ['forged-unsigned-path.jpg'], false)
                                .then(() => $wire.set('result', 'success'))
                                .catch(() => $wire.set('result', 'rejected'))
                        "
                    >Forge Upload</button>

                    <span dusk="result">{{ $result }}</span>
                </div>
                HTML; }
        })
        ->assertSeeIn('@result', 'pending')
        ->click('@forge')
        ->pause(500)
        ->assertSeeIn('@result', 'rejected')
        ;
    }

    public function test_finish_upload_rejects_paths_with_invalid_signature()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;
            public $result = 'pending';

            function mount()
            {
                Storage::disk('tmp-for-tests')->deleteDirectory('photos');
            }

            function render() { return <<<'HTML'
                <div>
                    <input type="file" wire:model="photo" dusk="upload">

                    <button
                        dusk="forge"
                        x-on:click="
                            $wire.call('_finishUpload', 'photo', ['invalidsignature:somefile.jpg'], false)
                                .then(() => $wire.set('result', 'success'))
                                .catch(() => $wire.set('result', 'rejected'))
                        "
                    >Forge Upload</button>

                    <span dusk="result">{{ $result }}</span>
                </div>
                HTML; }
        })
        ->assertSeeIn('@result', 'pending')
        ->click('@forge')
        ->pause(500)
        ->assertSeeIn('@result', 'rejected')
        ;
    }

    public function test_local_upload_succeeds_when_the_proxys_forwarded_https_origin_is_not_trusted()
    {
        // Signs under https, then swaps https:// for http:// on the URL —
        // simulating a proxy Laravel doesn't trust to report its real origin.
        $this->beforeServingApplication(function () {
            Storage::persistentFake('tmp-for-tests');

            GenerateSignedUploadUrlFacade::swap(new class extends GenerateSignedUploadUrl {
                public function forLocal()
                {
                    URL::forceScheme('https');
                    $url = parent::forLocal();
                    URL::forceScheme(null);

                    return preg_replace('#^https://#', 'http://', $url);
                }
            });

            Livewire::component('https-signed-upload', HttpsSignedUploadComponent::class);

            Route::get('/https-signed-upload', HttpsSignedUploadComponent::class)->middleware('web');
        });

        $this->browse(function ($browser) {
            $browser->visit('/https-signed-upload')
                ->assertMissing('@preview')
                ->attach('@upload', __DIR__ . '/browser_test_image.png')
                ->waitFor('@preview')
                ->assertVisible('@preview')
            ;
        });
    }

    public function test_cancel_upload_does_not_block_subsequent_uploads_on_same_property()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function render() { return <<<'HTML'
                <div>
                    <input type="file" wire:model="photo" dusk="upload">

                    <button type="button" wire:click="$cancelUpload('photo')" dusk="cancel">cancel</button>

                    <div>
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" dusk="preview">
                            <span dusk="filename">{{ $photo->getClientOriginalName() }}</span>
                        @endif
                    </div>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        // Hang binary upload XHRs so the first item stays in the bag long enough to queue a second...
        ->tap(fn ($b) => $b->script([<<<'JS'
            window.__hungUploads = true
            let NativeXHR = window.XMLHttpRequest

            window.XMLHttpRequest = function () {
                let xhr = new NativeXHR()
                let send = xhr.send.bind(xhr)

                xhr.send = function (body) {
                    // Leave the request open; cancelUpload can abort it later.
                    if (window.__hungUploads && body instanceof FormData) return

                    return send(body)
                }

                return xhr
            }

            window.XMLHttpRequest.prototype = NativeXHR.prototype
        JS]))
        // A starts: the signed URL roundtrip finishes, then its binary request hangs...
        ->waitForLivewire()->attach('@upload', __DIR__.'/browser_test_image.png')
        // B queues behind A → upload bag is [A, B]...
        ->attach('@upload', __DIR__.'/browser_test_image2.png')
        ->tap(fn ($b) => $b->script('window.__hungUploads = false'))
        // Cancel A, should start B.
        ->click('@cancel')
        ->waitFor('@preview')
        ->waitForTextIn('@filename', 'browser_test_image2.png');
    }
}

class HttpsSignedUploadComponent extends Component
{
    use WithFileUploads;

    public $photo;

    function render() {
        return <<<'HTML'
            <div>
                <input type="file" wire:model="photo" dusk="upload">

                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" dusk="preview">
                @endif
            </div>
        HTML;
    }
}
