<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\WithFileUploads;
use Livewire\Component;
use Livewire\Livewire;

class UploadActionBrowserTest extends \Tests\BrowserTestCase
{
    public function test_paste_uploads_files_from_the_clipboard()
    {
        \Illuminate\Support\Facades\Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $body = '';
            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <textarea dusk="box" wire:model="body" wire:paste="$upload('photos')"></textarea>

                <span dusk="name" x-text="$wire.photos[0]?.name"></span>
                <span dusk="status" x-text="$wire.photos[0] ? ($wire.photos[0].isUploading ? 'uploading' : 'finished') : 'empty'"></span>
            </div>
            HTML; }
        })
        ->assertSeeIn('@status', 'empty')
        ->tap(fn ($b) => $b->script(<<<'JS'
            let data = new DataTransfer()
            data.items.add(new File(['fake-image-content'], 'pasted.png', { type: 'image/png' }))

            document.querySelector('[dusk="box"]').dispatchEvent(
                new ClipboardEvent('paste', { clipboardData: data, bubbles: true, cancelable: true })
            )
        JS))
        ->waitForTextIn('@name', 'pasted.png')
        ->waitForTextIn('@status', 'finished')
        ;
    }

    public function test_text_pastes_are_left_alone()
    {
        \Illuminate\Support\Facades\Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $body = '';
            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <textarea dusk="box" wire:model="body" wire:paste="$upload('photos')"></textarea>

                <span dusk="count" x-text="$wire.photos.length"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            let data = new DataTransfer()
            data.setData('text/plain', 'just some text')

            let event = new ClipboardEvent('paste', { clipboardData: data, bubbles: true, cancelable: true })

            document.querySelector('[dusk="box"]').dispatchEvent(event)

            window.__pasteDefaultPrevented = event.defaultPrevented
        JS))
        ->pause(300)
        ->assertSeeIn('@count', '0')
        // A text paste must never be hijacked from the browser...
        ->waitUntil('window.__pasteDefaultPrevented === false')
        ;
    }

    public function test_drop_uploads_dropped_files_and_tracks_drag_state()
    {
        \Illuminate\Support\Facades\Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <div dusk="zone" wire:drop="$upload('photos')">Drop files here</div>

                <span dusk="name" x-text="$wire.photos[0]?.name"></span>
                <span dusk="status" x-text="$wire.photos[0] ? ($wire.photos[0].isUploading ? 'uploading' : 'finished') : 'empty'"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            window.__zone = document.querySelector('[dusk="zone"]')

            window.__fileDrag = type => {
                let data = new DataTransfer()
                data.items.add(new File(['fake-image-content'], 'dropped.png', { type: 'image/png' }))

                return new DragEvent(type, { dataTransfer: data, bubbles: true, cancelable: true })
            }

            window.__zone.dispatchEvent(window.__fileDrag('dragenter'))
        JS))
        // A file drag over the zone reflects as data-dragging...
        ->waitUntil('document.querySelector(\'[dusk="zone"]\').hasAttribute(\'data-dragging\')')
        ->tap(fn ($b) => $b->script('window.__zone.dispatchEvent(window.__fileDrag("dragleave"))'))
        ->waitUntil('! document.querySelector(\'[dusk="zone"]\').hasAttribute(\'data-dragging\')')
        ->tap(fn ($b) => $b->script(<<<'JS'
            window.__zone.dispatchEvent(window.__fileDrag('dragenter'))
            window.__zone.dispatchEvent(window.__fileDrag('drop'))
        JS))
        ->waitUntil('! document.querySelector(\'[dusk="zone"]\').hasAttribute(\'data-dragging\')')
        ->waitForTextIn('@name', 'dropped.png')
        ->waitForTextIn('@status', 'finished')
        ;
    }

    public function test_text_drags_are_ignored_by_dropzones()
    {
        \Illuminate\Support\Facades\Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <div dusk="zone" wire:drop="$upload('photos')">Drop files here</div>

                <span dusk="count" x-text="$wire.photos.length"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            let data = new DataTransfer()
            data.setData('text/plain', 'dragged text')

            document.querySelector('[dusk="zone"]').dispatchEvent(
                new DragEvent('dragenter', { dataTransfer: data, bubbles: true, cancelable: true })
            )
        JS))
        ->pause(300)
        ->assertAttributeMissing('@zone', 'data-dragging')
        ->assertSeeIn('@count', '0')
        ;
    }

    public function test_window_modifier_accepts_drops_anywhere_on_the_page()
    {
        \Illuminate\Support\Facades\Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <div dusk="overlay" wire:drop.window="$upload('photos')">Drop anywhere</div>

                <p dusk="outside">Somewhere else entirely on the page</p>

                <span dusk="name" x-text="$wire.photos[0]?.name"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            let data = new DataTransfer()
            data.items.add(new File(['fake-image-content'], 'window-dropped.png', { type: 'image/png' }))

            let outside = document.querySelector('[dusk="outside"]')

            outside.dispatchEvent(new DragEvent('dragenter', { dataTransfer: data, bubbles: true, cancelable: true }))

            window.__draggingDuringDrag = document.querySelector('[dusk="overlay"]').hasAttribute('data-dragging')

            outside.dispatchEvent(new DragEvent('drop', { dataTransfer: data, bubbles: true, cancelable: true }))
        JS))
        // The drag state lands on the directive's element, not the drop target...
        ->waitUntil('window.__draggingDuringDrag === true')
        ->waitForTextIn('@name', 'window-dropped.png')
        ;
    }

    public function test_click_opens_the_file_picker_and_uploads_the_selection()
    {
        \Illuminate\Support\Facades\Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <button dusk="add" type="button" wire:click="$upload('photos')">Add files</button>

                <span dusk="name" x-text="$wire.photos[0]?.name"></span>
            </div>
            HTML; }
        })
        // Headless Chrome auto-dismisses file choosers (firing `cancel`, which
        // cleans the picker input up before we can reach it) — intercept the
        // chooser so the input stays put and we can deliver a selection the
        // way the OS dialog would...
        ->tap(fn ($b) => $b->driver->executeCustomCommand(
            '/session/:sessionId/goog/cdp/execute',
            'POST',
            ['cmd' => 'Page.setInterceptFileChooserDialog', 'params' => ['enabled' => true]],
        ))
        ->click('@add')
        ->waitUntil('document.querySelector(\'input[data-livewire-picker="photos"]\') !== null')
        ->tap(fn ($b) => $b->script(<<<'JS'
            let input = document.querySelector('input[data-livewire-picker="photos"]')

            window.__pickerWasMultiple = input.multiple

            let data = new DataTransfer()
            data.items.add(new File(['fake-image-content'], 'picked.png', { type: 'image/png' }))

            input.files = data.files

            input.dispatchEvent(new Event('change'))
        JS))
        ->waitForTextIn('@name', 'picked.png')
        // An array property implies a multiple-file picker...
        ->waitUntil('window.__pickerWasMultiple === true')
        // The picker input cleans up after itself...
        ->waitUntil('document.querySelector(\'input[data-livewire-picker]\') === null')
        ;
    }

    public function test_accept_option_filters_incoming_files()
    {
        \Illuminate\Support\Facades\Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <div dusk="zone" wire:drop="$upload('photos', { accept: 'image/*' })">Drop images here</div>

                <span dusk="count" x-text="$wire.photos.length"></span>
                <span dusk="name" x-text="$wire.photos[0]?.name"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            let data = new DataTransfer()
            data.items.add(new File(['not-an-image'], 'notes.txt', { type: 'text/plain' }))
            data.items.add(new File(['fake-image-content'], 'photo.png', { type: 'image/png' }))

            document.querySelector('[dusk="zone"]').dispatchEvent(
                new DragEvent('drop', { dataTransfer: data, bubbles: true, cancelable: true })
            )
        JS))
        ->waitForTextIn('@name', 'photo.png')
        ->waitUntil('document.querySelector(\'[dusk="count"]\').textContent === "1"')
        ;
    }

    public function test_single_file_property_takes_only_the_first_file()
    {
        \Illuminate\Support\Facades\Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function render() { return <<<'HTML'
            <div>
                <div dusk="zone" wire:drop="$upload('photo')">Drop a file here</div>

                <span dusk="name" x-text="$wire.photo?.name"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            let data = new DataTransfer()
            data.items.add(new File(['fake-image-content'], 'first.png', { type: 'image/png' }))
            data.items.add(new File(['fake-image-content'], 'second.png', { type: 'image/png' }))

            document.querySelector('[dusk="zone"]').dispatchEvent(
                new DragEvent('drop', { dataTransfer: data, bubbles: true, cancelable: true })
            )
        JS))
        ->waitForTextIn('@name', 'first.png')
        ;
    }

    public function test_legacy_upload_callback_signature_still_works()
    {
        \Illuminate\Support\Facades\Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function render() { return <<<'HTML'
            <div>
                <span dusk="name" x-text="$wire.photo?.name"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            let component = window.Livewire.all()[0]

            component.$wire.$upload(
                'photo',
                new File(['fake-image-content'], 'legacy.png', { type: 'image/png' }),
                () => window.__legacyFinished = true
            )
        JS))
        ->waitForTextIn('@name', 'legacy.png')
        ->waitUntil('window.__legacyFinished === true')
        ;
    }
}
