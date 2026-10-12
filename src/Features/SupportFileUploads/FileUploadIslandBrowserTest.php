<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithFileUploads;

class FileUploadIslandBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config()->set('livewire.temporary_file_upload.rules', 'required|image|max:10240');
        };
    }

    public function test_native_file_upload_inside_an_island_only_renders_the_island()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $file;
            public $outside;

            public function render() { return <<<'HTML'
            <div>
                <p dusk="base">{{ microtime(true) }}</p>
                <input type="file" wire:model="outside" dusk="outside-upload">
                @if ($outside) <p dusk="outside-name">{{ $outside->getClientOriginalName() }}</p> @endif

                @island(name: 'file-upload')
                    <p dusk="island">{{ microtime(true) }}</p>
                    <input type="file" wire:model="file" dusk="island-upload">
                    @if ($file) <p dusk="file-name">{{ $file->getClientOriginalName() }}</p> @endif
                @endisland
            </div>
            HTML; }
        })
        ->tap(function ($browser) {
            $browser->script('window.initialBase = document.querySelector(\'[dusk="base"]\').textContent; window.initialIsland = document.querySelector(\'[dusk="island"]\').textContent');
        })
        ->attach('@island-upload', __DIR__ . '/browser_test_image.png')
        ->waitForTextIn('@file-name', 'browser_test_image.png')
        ->assertScript('document.querySelector(\'[dusk="base"]\').textContent === window.initialBase', true)
        ->assertScript('document.querySelector(\'[dusk="island"]\').textContent !== window.initialIsland', true)
        ->attach('@outside-upload', __DIR__ . '/browser_test_image.png')
        ->waitForTextIn('@outside-name', 'browser_test_image.png')
        ->assertScript('document.querySelector(\'[dusk="base"]\').textContent !== window.initialBase', true);
    }

    public function test_native_file_upload_errors_inside_an_island_only_render_the_island()
    {
        Storage::persistentFake('tmp-for-tests');

        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $file;

            public function render() { return <<<'HTML'
            <div>
                <p dusk="base">{{ microtime(true) }}</p>
                <p dusk="base-error">{{ $errors->first('file') }}</p>
                @island(name: 'file-upload')
                    <p dusk="island">{{ microtime(true) }}</p>
                    <input type="file" wire:model="file" dusk="island-upload">
                    <p dusk="island-error">{{ $errors->first('file') }}</p>
                @endisland
            </div>
            HTML; }
        })
        ->tap(function ($browser) {
            $browser->script('window.initialBase = document.querySelector(\'[dusk="base"]\').textContent; window.initialIsland = document.querySelector(\'[dusk="island"]\').textContent');
        })
        ->attach('@island-upload', __DIR__ . '/../../../tests/fixtures/download.txt')
        ->waitForTextIn('@island-error', 'The file field must be an image.')
        ->assertDontSeeIn('@base-error', 'The file field must be an image.')
        ->assertScript('document.querySelector(\'[dusk="base"]\').textContent === window.initialBase', true)
        ->assertScript('document.querySelector(\'[dusk="island"]\').textContent !== window.initialIsland', true)
        ->assertScript('window.Livewire.all()[0].$wire.file', null);
    }
}
