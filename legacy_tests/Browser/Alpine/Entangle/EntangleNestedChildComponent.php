<?php

namespace LegacyTests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class EntangleNestedChildComponent extends BaseComponent
{
    public $item;

    protected $rules = ['item.name' => ''];

    public function render()
    {
        return
<<<'HTML'
<div x-data="{ name: @entangle('item.name') }">
    <div dusk="livewire-output-{{ $item['name']}}">{{ $item['name']}}</div>
    <div dusk="alpine-output-{{ $item['name']}}"><span x-text="name"></span></div>
</div>
HTML;
    }
}
