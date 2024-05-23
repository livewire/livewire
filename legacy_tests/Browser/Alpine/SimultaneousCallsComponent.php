<?php

namespace LegacyTests\Browser\Alpine;

use Livewire\Component as BaseComponent;

class SimultaneousCallsComponent extends BaseComponent
{
    public function returnValue($value)
    {
        return $value;
    }

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data="{ foo: '...', bar: '...' }">
        <div x-on:click="bar = await $wire.returnValue('bar')">
            <div x-on:click="foo = await $wire.returnValue('foo')">
                <button dusk="update-foo-and-bar">Update foo and bar at the same time:</button>
            </div>
        </div>

        foo: <span x-text="foo" dusk="foo"></span>
        bar: <span x-text="bar" dusk="bar"></span>
    </div>
</div>
HTML;
    }
}
