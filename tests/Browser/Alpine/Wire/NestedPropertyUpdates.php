<?php

namespace Tests\Browser\Alpine\Wire;

use Livewire\Component as BaseComponent;

class NestedPropertyUpdates extends BaseComponent
{
    public $foo = [
        'bar' => [
            'bob' => 'baz',
        ]
    ];

    public $fizz = [
        'buzz',
    ];

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data="{}">
        <p dusk="foo-server">{{ $foo['bar']['bob'] }}</p>

        <input type="text" dusk="foo-input" x-model="$wire.foo.bar.bob">

        <p dusk="fizz-server">{{ $fizz[0] }}</p>

        <input type="text" dusk="fizz-input" x-model="$wire.fizz[0]">
    </div>
</div>
HTML;
    }
}
