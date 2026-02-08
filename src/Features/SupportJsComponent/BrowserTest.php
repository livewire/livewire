<?php

namespace Livewire\Features\SupportJsComponent;

use Livewire\Livewire;

// Tools like Laravel Boost call JSON.stringify() on objects they encounter
// (e.g. when logging to the browser console). Without a toJSON() method,
// stringifying $wire triggers the Proxy's getter traps which fires a server
// request for a non-existent "toJSON" PHP method, and stringifying the
// Component instance hits a circular reference (el <-> component).
//
// Both $wire and Component now define toJSON() to return a clean, serialisable
// snapshot so that JSON.stringify() works safely in these scenarios.

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_wire_can_be_json_stringified_without_triggering_server_request()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $title = 'Taylor';

                public $count = 0;

                public function render() { return <<<'HTML'
                <div>
                    <button dusk="stringify" @click="window.result = JSON.stringify($wire)">Stringify</button>

                    <span dusk="count" wire:text="count"></span>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->click('@stringify')
        ->assertSeeIn('@count', '0')
        // Assert id, name, and key are present in the output...
        ->assertScript('(() => { let r = JSON.parse(window.result); return "id" in r && "name" in r && "key" in r })()', true)
        // Replace dynamic values with fixed strings so we can assert the full structure...
        ->assertScript('(() => { let r = JSON.parse(window.result); r.id = "ID"; r.name = "NAME"; r.key = "KEY"; return JSON.stringify(r) })()',
            json_encode([
                'id' => 'ID',
                'name' => 'NAME',
                'key' => 'KEY',
                'data' => [
                    'title' => 'Taylor',
                    'count' => 0,
                ],
            ]),
        )
        ;
    }

    public function test_component_can_be_json_stringified_without_circular_reference_error()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $title = 'Taylor';

                public function render() { return <<<'HTML'
                <div>
                    <button dusk="stringify" @click="
                        let component = $wire.__instance;
                        window.result = JSON.stringify(component);
                    ">Stringify</button>
                </div>
                HTML; }
        })
        ->waitForLivewireToLoad()
        ->click('@stringify')
        // Assert id, name, and key are present in the output...
        ->assertScript('(() => { let r = JSON.parse(window.result); return "id" in r && "name" in r && "key" in r })()', true)
        // Replace dynamic values with fixed strings so we can assert the full structure...
        ->assertScript('(() => { let r = JSON.parse(window.result); r.id = "ID"; r.name = "NAME"; r.key = "KEY"; return JSON.stringify(r) })()',
            json_encode([
                'id' => 'ID',
                'name' => 'NAME',
                'key' => 'KEY',
                'data' => [
                    'title' => 'Taylor',
                ],
            ]),
        )
        ;
    }
}
