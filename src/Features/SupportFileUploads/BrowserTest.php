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

    public function test_file_uploads_hydrate_into_rich_js_objects()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="photo" dusk="upload">

                <span dusk="name" x-text="$wire.photo?.name"></span>
                <span dusk="extension" x-text="$wire.photo?.extension"></span>

                <template x-if="$wire.photo?.isPreviewable">
                    <img dusk="preview" x-bind:src="$wire.photo.temporaryUrl()">
                </template>
            </div>
            HTML; }
        })
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        // The rich object exposes the original client-side filename...
        ->waitForTextIn('@name', 'browser_test_image.png')
        ->assertSeeIn('@extension', 'png')
        // And a preview URL usable entirely from JavaScript — the image must actually load...
        ->waitFor('@preview')
        ->waitUntil('document.querySelector(\'[dusk="preview"]\').naturalWidth > 0');
    }

    public function test_rich_upload_objects_expose_reactive_progress_state_and_client_side_previews()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="photo" dusk="upload">

                {{-- Log every reactive change to the upload's lifecycle state... --}}
                <div x-effect="window.__log = window.__log || []; window.__log.push($wire.photo ? [$wire.photo.progress, $wire.photo.isUploading] : null)"></div>

                <template x-if="$wire.photo">
                    <img dusk="preview" x-bind:src="$wire.photo.previewUrl">
                </template>

                <span dusk="status" x-text="$wire.photo ? ($wire.photo.isUploading ? 'uploading' : 'finished') : 'empty'"></span>
            </div>
            HTML; }
        })
        ->assertSeeIn('@status', 'empty')
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->waitForTextIn('@status', 'finished')
        // The property held a pending object during the upload (progress state
        // was observable before the server ever responded)...
        ->assertScript('window.__log.some(entry => entry && entry[1] === true)', true)
        // ...and settled at progress 100 / not uploading...
        ->assertScript('window.__log[window.__log.length - 1][0]', 100)
        ->assertScript('window.__log[window.__log.length - 1][1]', false)
        // The preview never needed the server: it's a local blob URL, live
        // during the upload AND after it settled...
        ->assertScript('document.querySelector(\'[dusk="preview"]\').src.startsWith("blob:")', true)
        ->waitUntil('document.querySelector(\'[dusk="preview"]\').naturalWidth > 0')
        // And the settled object still exposes its server-side wire identity...
        ->assertScript('window.Livewire.all()[0].$wire.photo.serialized.startsWith("livewire-file:")', true);
    }

    public function test_rich_upload_objects_can_remove_themselves()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="photo" dusk="upload">

                <span dusk="status" x-text="$wire.photo ? $wire.photo.name : 'empty'"></span>

                <button dusk="remove" type="button" x-on:click="$wire.photo.remove(() => window.__removed = true)">Remove</button>
            </div>
            HTML; }
        })
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->waitForTextIn('@status', 'browser_test_image.png')
        ->tap(fn ($b) => $b->script('window.__errs = []; window.addEventListener("error", e => window.__errs.push(e.message)); window.__removed = false'))
        // Removal is optimistic: the property nulls in the same synchronous
        // frame as the click — no server round trip could have completed...
        ->assertScript('(() => { document.querySelector(\'[dusk="remove"]\').click(); return window.Livewire.all()[0].$wire.photo === null })()', true)
        ->waitForTextIn('@status', 'empty')
        // The removal must complete cleanly: no duplicate-manager errors (rich
        // objects reach their component through Alpine proxies, which must
        // resolve to the same upload manager as the wire:model directive)...
        ->assertScript('window.__errs.length', 0)
        // ...and the server-confirmation finish callback must still fire...
        ->waitUntil('window.__removed === true')
        // Removing also clears the file input so the same file can be re-selected...
        ->assertScript('document.querySelector(\'[dusk="upload"]\').value', '');
    }

    public function test_multiple_file_uploads_hydrate_into_arrays_of_rich_objects_and_support_removal()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="photos" dusk="upload" multiple>

                <span dusk="names" x-text="$wire.photos.map(photo => photo.name).join(',')"></span>

                <button dusk="remove-first" type="button" x-on:click="$wire.photos[0].remove()">Remove first</button>
            </div>
            HTML; }
        })
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->waitUntil('window.Livewire.all()[0].$wire.photos.length === 1')
        // Dusk's attach() accumulates files on a multiple input, so clear the
        // selection between attaches to avoid re-uploading the first file...
        ->tap(fn ($b) => $b->script('document.querySelector(\'[dusk="upload"]\').value = null'))
        ->attach('@upload', __DIR__ . '/browser_test_image2.png')
        ->waitUntil('window.Livewire.all()[0].$wire.photos.length === 2')
        ->assertSeeIn('@names', 'browser_test_image.png,browser_test_image2.png')
        // Removal is optimistic: the array shrinks in the same synchronous
        // frame as the click, before any server round trip...
        ->assertScript('(() => { document.querySelector(\'[dusk="remove-first"]\').click(); return window.Livewire.all()[0].$wire.photos.length })()', 1)
        ->waitForTextIn('@names', 'browser_test_image2.png')
        ->assertDontSeeIn('@names', 'browser_test_image.png,')
        // And the server settles on the same state (no reconciliation flicker)...
        ->pause(500)
        ->assertScript('window.Livewire.all()[0].$wire.photos.length', 1)
        ->assertSeeIn('@names', 'browser_test_image2.png');
    }

    public function test_uploading_to_an_array_property_through_an_input_missing_the_multiple_attribute_appends_instead_of_replacing()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="photos" dusk="upload">

                {{-- Log the property's shape on every reactive change... --}}
                <div x-effect="window.__shapes = window.__shapes || []; window.__shapes.push(Array.isArray($wire.photos))"></div>

                <span dusk="names" x-text="Array.isArray($wire.photos) ? $wire.photos.map(photo => photo.name).join(',') : 'NOT-AN-ARRAY'"></span>
            </div>
            HTML; }
        })
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->waitForTextIn('@names', 'browser_test_image.png')
        // The property stayed an array through the entire upload lifecycle —
        // the pending object was appended into it, never swapped in as a
        // bare object out from under x-for loops...
        ->assertScript('window.__shapes.every(shape => shape === true)', true)
        ->assertScript('window.Livewire.all()[0].$wire.photos.length', 1)
        // A second selection through the same single input appends, matching
        // the server's long-standing behavior for array properties...
        ->attach('@upload', __DIR__ . '/browser_test_image2.png')
        ->waitUntil('window.Livewire.all()[0].$wire.photos.length === 2')
        ->assertSeeIn('@names', 'browser_test_image.png,browser_test_image2.png');
    }

    public function test_upload_violating_property_size_rules_is_rejected_before_uploading_and_never_attached()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            #[BaseValidate('image|max:1024')]
            public $photo;

            function mount()
            {
                Storage::disk('tmp-for-tests')->deleteDirectory('livewire-tmp');
            }

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="photo" dusk="upload">

                @error('photo') <span dusk="error">{{ $message }}</span> @enderror

                <div>
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" dusk="preview">
                    @endif
                </div>
            </div>
            HTML; }
        })
        ->assertMissing('@error')
        // This file is ~1041KB — over the property's 1024KB max...
        ->attach('@upload', __DIR__ . '/browser_test_image_big.jpg')
        ->waitFor('@error')
        ->assertSeeIn('@error', 'The photo field must not be greater than 1024 kilobytes.')
        ->assertMissing('@preview')
        ->tap(function () {
            // The declared size was rejected during the handshake — no bytes ever moved...
            $this->assertEmpty(Storage::disk('tmp-for-tests')->files('livewire-tmp'));
        })
        // A valid file afterwards uploads normally and clears the error...
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->waitFor('@preview')
        ->assertMissing('@error')
        ;
    }

    public function test_upload_violating_property_type_rules_is_rejected_before_uploading_and_never_attached()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            #[BaseValidate('image|max:1024')]
            public $photo;

            function mount()
            {
                Storage::disk('tmp-for-tests')->deleteDirectory('livewire-tmp');
            }

            function render() { return <<<'HTML'
            <div>
                <input type="file" wire:model="photo" dusk="upload">

                @error('photo') <span dusk="error">{{ $message }}</span> @enderror

                <div>
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" dusk="preview">
                    @endif
                </div>
            </div>
            HTML; }
        })
        ->assertMissing('@error')
        // A text document against an "image" rule — the declared name and
        // MIME type both prove the violation, so it's rejected during the
        // plan handshake...
        ->attach('@upload', __DIR__ . '/browser_test_document.txt')
        ->waitFor('@error')
        ->assertSeeIn('@error', 'The photo field must be an image.')
        ->assertMissing('@preview')
        ->tap(function () {
            // The rejection came from declared metadata — no bytes ever moved...
            $this->assertEmpty(Storage::disk('tmp-for-tests')->files('livewire-tmp'));
        })
        // A valid image afterwards uploads normally and clears the error...
        ->attach('@upload', __DIR__ . '/browser_test_image.png')
        ->waitFor('@preview')
        ->assertMissing('@error')
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
}
