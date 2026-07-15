<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Storage;
use Facebook\WebDriver\WebDriverKeys;
use Livewire\WithFileUploads;
use Livewire\Component;
use Livewire\Livewire;

/**
 * These tests drive real browser input wherever the platform allows it:
 * real clipboard contents delivered by real paste keystrokes, browser-
 * generated drag events carrying real files from disk (via CDP), and a
 * real click opening a real file chooser. The JS-door tests construct
 * File objects directly because that IS how the JS API is used.
 */
class UploadActionBrowserTest extends \Tests\BrowserTestCase
{
    public function test_paste_uploads_files_from_the_clipboard()
    {
        Storage::persistentFake('tmp-for-tests');

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
        ->tap(fn ($b) => $this->putImageOnClipboard($b))
        ->click('@box')
        ->keys('@box', [$this->pasteChord(), 'v'])
        // Chrome names pasted image data "image.png"...
        ->waitForTextIn('@name', 'image.png')
        ->waitForTextIn('@status', 'finished')
        ;
    }

    public function test_text_pastes_are_left_alone()
    {
        Storage::persistentFake('tmp-for-tests');

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
        ->tap(fn ($b) => $this->putTextOnClipboard($b, 'just some text'))
        ->click('@box')
        ->keys('@box', [$this->pasteChord(), 'v'])
        // The browser's default paste must actually happen...
        ->waitUntil('document.querySelector(\'[dusk="box"]\').value === "just some text"')
        ->assertSeeIn('@count', '0')
        ;
    }

    public function test_drop_uploads_dropped_files_and_tracks_drag_state()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <div dusk="zone" wire:drop.file="$upload('photos')" style="width: 300px; height: 100px">Drop files here</div>

                <div dusk="elsewhere" style="width: 300px; height: 100px">Somewhere else</div>

                <span dusk="name" x-text="$wire.photos[0]?.name"></span>
                <span dusk="status" x-text="$wire.photos[0] ? ($wire.photos[0].isUploading ? 'uploading' : 'finished') : 'empty'"></span>
            </div>
            HTML; }
        })
        // Drag a real file over the zone...
        ->tap(fn ($b) => $this->dragFileOver($b, '@zone', [__DIR__.'/browser_test_image.png']))
        ->waitUntil('document.querySelector(\'[dusk="zone"]\').hasAttribute(\'data-dragging\')')
        // Drag it away — the browser generates the real dragleave...
        ->tap(fn ($b) => $this->dragFileOver($b, '@elsewhere', [__DIR__.'/browser_test_image.png']))
        ->waitUntil('! document.querySelector(\'[dusk="zone"]\').hasAttribute(\'data-dragging\')')
        // Drag back and drop...
        ->tap(fn ($b) => $this->dragFileOver($b, '@zone', [__DIR__.'/browser_test_image.png']))
        ->tap(fn ($b) => $this->dropFiles($b, '@zone', [__DIR__.'/browser_test_image.png']))
        ->waitUntil('! document.querySelector(\'[dusk="zone"]\').hasAttribute(\'data-dragging\')')
        ->waitForTextIn('@name', 'browser_test_image.png')
        ->waitForTextIn('@status', 'finished')
        ;
    }

    public function test_file_modifier_scopes_dropzones_to_file_drags()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <div dusk="zone" wire:drop.file="$upload('photos')" style="width: 300px; height: 100px">Drop files here</div>

                <span dusk="count" x-text="$wire.photos.length"></span>
            </div>
            HTML; }
        })
        // A drag carrying only text (no files) over the zone...
        ->tap(fn ($b) => $this->dispatchDrag($b, 'dragEnter', '@zone', [], [['mimeType' => 'text/plain', 'data' => 'dragged text']]))
        ->pause(300)
        ->assertAttributeMissing('@zone', 'data-dragging')
        ->assertSeeIn('@count', '0')
        ;
    }

    public function test_bare_dropzones_are_general_and_evaluate_any_drop()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $dropped = '';

            function handleDrop($text)
            {
                $this->dropped = $text;
            }

            function render() { return <<<'HTML'
            <div>
                <div dusk="zone" wire:drop="handleDrop($event.dataTransfer.getData('text/plain'))" style="width: 300px; height: 100px">Drop anything here</div>

                <span dusk="dropped">{{ $dropped }}</span>
            </div>
            HTML; }
        })
        // Without .file, any drag engages the zone...
        ->tap(fn ($b) => $this->dispatchDrag($b, 'dragEnter', '@zone', [], [['mimeType' => 'text/plain', 'data' => 'dragged text']]))
        ->waitUntil('document.querySelector(\'[dusk="zone"]\').hasAttribute(\'data-dragging\')')
        // ...and any drop evaluates the expression, with $event in scope...
        ->tap(fn ($b) => $this->dispatchDrag($b, 'drop', '@zone', [], [['mimeType' => 'text/plain', 'data' => 'dragged text']]))
        ->waitForTextIn('@dropped', 'dragged text')
        ->waitUntil('! document.querySelector(\'[dusk="zone"]\').hasAttribute(\'data-dragging\')')
        ;
    }

    public function test_file_modifier_filters_pastes_to_files()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $body = '';
            public $pastes = 0;

            function render() { return <<<'HTML'
            <div>
                <textarea dusk="box" wire:model="body" wire:paste.file="$set('pastes', pastes + 1)"></textarea>

                <span dusk="pastes" x-text="$wire.pastes"></span>
            </div>
            HTML; }
        })
        // A real text paste doesn't trigger the filtered listener...
        ->tap(fn ($b) => $this->putTextOnClipboard($b, 'just some text'))
        ->click('@box')
        ->keys('@box', [$this->pasteChord(), 'v'])
        ->waitUntil('document.querySelector(\'[dusk="box"]\').value === "just some text"')
        ->assertSeeIn('@pastes', '0')
        // A real file paste does...
        ->tap(fn ($b) => $this->putImageOnClipboard($b))
        ->click('@box')
        ->keys('@box', [$this->pasteChord(), 'v'])
        ->waitForTextIn('@pastes', '1')
        ;
    }

    public function test_window_modifier_accepts_drops_anywhere_on_the_page()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <div dusk="overlay" wire:drop.file.window="$upload('photos')">Drop anywhere</div>

                <p dusk="outside" style="margin-top: 100px">Somewhere else entirely on the page</p>

                <span dusk="name" x-text="$wire.photos[0]?.name"></span>
            </div>
            HTML; }
        })
        // Drag over an element that is NOT the directive's element...
        ->tap(fn ($b) => $this->dragFileOver($b, '@outside', [__DIR__.'/browser_test_image.png']))
        // The drag state lands on the directive's element...
        ->waitUntil('document.querySelector(\'[dusk="overlay"]\').hasAttribute(\'data-dragging\')')
        ->tap(fn ($b) => $this->dropFiles($b, '@outside', [__DIR__.'/browser_test_image.png']))
        ->waitForTextIn('@name', 'browser_test_image.png')
        ;
    }

    public function test_click_opens_the_file_picker_and_uploads_the_selection()
    {
        Storage::persistentFake('tmp-for-tests');

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
        // cleans the picker input up before a selection can happen) — intercept
        // the chooser so the dialog stays "open"...
        ->tap(fn ($b) => $this->cdp($b, 'Page.setInterceptFileChooserDialog', ['enabled' => true]))
        ->click('@add')
        ->waitUntil('document.querySelector(\'input[data-livewire-picker="photos"]\') !== null')
        // An array property implies a multiple-file picker...
        ->tap(fn ($b) => $b->script('window.__pickerWasMultiple = document.querySelector(\'input[data-livewire-picker]\').multiple'))
        // Deliver the selection the way Chrome's own automation does — a real
        // file from disk, with the browser firing the trusted change event...
        ->tap(fn ($b) => $this->chooseFilesInPicker($b, [__DIR__.'/browser_test_image.png']))
        ->waitForTextIn('@name', 'browser_test_image.png')
        ->waitUntil('window.__pickerWasMultiple === true')
        // The picker input cleans up after itself...
        ->waitUntil('document.querySelector(\'input[data-livewire-picker]\') === null')
        ;
    }

    public function test_accept_option_filters_incoming_files()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <div dusk="zone" wire:drop="$upload('photos', { accept: 'image/*' })" style="width: 300px; height: 100px">Drop images here</div>

                <span dusk="count" x-text="$wire.photos.length"></span>
                <span dusk="name" x-text="$wire.photos[0]?.name"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $this->dropFiles($b, '@zone', [
            __DIR__.'/browser_test_document.txt',
            __DIR__.'/browser_test_image.png',
        ]))
        ->waitForTextIn('@name', 'browser_test_image.png')
        ->waitUntil('document.querySelector(\'[dusk="count"]\').textContent === "1"')
        ;
    }

    public function test_single_file_property_takes_only_the_first_file()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;

            function render() { return <<<'HTML'
            <div>
                <div dusk="zone" wire:drop="$upload('photo')" style="width: 300px; height: 100px">Drop a file here</div>

                <span dusk="name" x-text="$wire.photo?.name"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $this->dropFiles($b, '@zone', [
            __DIR__.'/browser_test_image.png',
            __DIR__.'/browser_test_image2.png',
        ]))
        ->waitForTextIn('@name', 'browser_test_image.png')
        ;
    }

    public function test_upload_promise_resolves_with_rich_upload_objects()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $photo;
            public $photos = [];

            function render() { return <<<'HTML'
            <div>
                <span dusk="name" x-text="$wire.photo?.name"></span>
            </div>
            HTML; }
        })
        ->tap(fn ($b) => $b->script(<<<'JS'
            (async () => {
                let $wire = window.Livewire.all()[0].$wire

                // A real 1x1 PNG, so the server's mime-sniffing marks it previewable...
                let png = () => Uint8Array.from(atob('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='), c => c.charCodeAt(0))

                // A single upload resolves the property's rich upload object...
                let photo = await $wire.$upload('photo', new File([png()], 'awaited.png', { type: 'image/png' }))

                // A multiple upload resolves an array holding just this batch...
                let batch = await $wire.$upload('photos', [
                    new File([png()], 'one.png', { type: 'image/png' }),
                    new File([png()], 'two.png', { type: 'image/png' }),
                ])

                window.__resolved = {
                    single: { name: photo.name, isUploading: photo.isUploading, previewable: photo.isPreviewable },
                    batch: batch.map(upload => upload.name),
                }
            })()
        JS))
        ->waitUntil('window.__resolved !== undefined')
        ->waitUntil('window.__resolved.single.name === "awaited.png" && window.__resolved.single.isUploading === false')
        ->waitUntil('JSON.stringify(window.__resolved.batch) === \'["one.png","two.png"]\'')
        ->assertScript('window.__resolved.single.previewable', true)
        ;
    }

    public function test_legacy_upload_callback_signature_still_works()
    {
        Storage::persistentFake('tmp-for-tests');

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

    // ─── Real-input helpers ─────────────────────────────────────────────

    protected function cdp($browser, $command, $params = [])
    {
        return $browser->driver->executeCustomCommand(
            '/session/:sessionId/goog/cdp/execute',
            'POST',
            ['cmd' => $command, 'params' => (object) $params],
        );
    }

    protected function pasteChord()
    {
        return PHP_OS_FAMILY === 'Darwin' ? WebDriverKeys::COMMAND : WebDriverKeys::CONTROL;
    }

    // Write a real PNG to the browser's actual clipboard so a real paste
    // keystroke delivers it as a trusted event...
    protected function putImageOnClipboard($browser)
    {
        $this->cdp($browser, 'Browser.grantPermissions', ['permissions' => ['clipboardReadWrite', 'clipboardSanitizedWrite']]);

        $browser->script(<<<'JS'
            (async () => {
                let canvas = document.createElement('canvas')
                canvas.width = canvas.height = 8
                let context = canvas.getContext('2d')
                context.fillStyle = 'red'
                context.fillRect(0, 0, 8, 8)

                let blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'))

                await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })])

                window.__clipboardReady = true
            })()
        JS);

        $browser->waitUntil('window.__clipboardReady === true');
    }

    protected function putTextOnClipboard($browser, $text)
    {
        $this->cdp($browser, 'Browser.grantPermissions', ['permissions' => ['clipboardReadWrite', 'clipboardSanitizedWrite']]);

        $browser->script("(async () => { await navigator.clipboard.writeText('{$text}'); window.__clipboardReady = true })()");

        $browser->waitUntil('window.__clipboardReady === true');
    }

    // Dispatch browser-generated drag events (via CDP) carrying real files
    // from disk — the browser derives the trusted dragenter/dragover/dragleave
    // stream from the pointer position, exactly like an OS file drag...
    protected function dispatchDrag($browser, $type, $selector, $files = [], $items = [])
    {
        [$x, $y] = $this->centerOf($browser, $selector);

        $this->cdp($browser, 'Input.dispatchDragEvent', [
            'type' => $type,
            'x' => $x,
            'y' => $y,
            'data' => ['items' => $items, 'files' => $files, 'dragOperationsMask' => 1],
        ]);
    }

    protected function dragFileOver($browser, $selector, $files)
    {
        $this->dispatchDrag($browser, 'dragEnter', $selector, $files);
        $this->dispatchDrag($browser, 'dragOver', $selector, $files);
    }

    protected function dropFiles($browser, $selector, $files)
    {
        $this->dragFileOver($browser, $selector, $files);
        $this->dispatchDrag($browser, 'drop', $selector, $files);
    }

    protected function centerOf($browser, $selector)
    {
        $selector = str_starts_with($selector, '@') ? '[dusk="'.substr($selector, 1).'"]' : $selector;

        return $browser->script("let rect = document.querySelector('{$selector}').getBoundingClientRect(); return [rect.x + rect.width / 2, rect.y + rect.height / 2]")[0];
    }

    // Deliver a picker selection the way Chrome's own automation does:
    // DOM.setFileInputFiles sets real files from disk on the input and the
    // browser fires the trusted change event...
    protected function chooseFilesInPicker($browser, $files)
    {
        $document = $this->cdp($browser, 'DOM.getDocument');

        $input = $this->cdp($browser, 'DOM.querySelector', [
            'nodeId' => $document['root']['nodeId'],
            'selector' => 'input[data-livewire-picker]',
        ]);

        $this->cdp($browser, 'DOM.setFileInputFiles', [
            'files' => $files,
            'nodeId' => $input['nodeId'],
        ]);
    }
}
