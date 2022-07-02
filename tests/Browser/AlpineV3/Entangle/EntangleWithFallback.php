<?php

namespace Tests\Browser\AlpineV3\Entangle;

use Livewire\Component as BaseComponent;

class EntangleWithFallback extends BaseComponent
{
    public $items = [];

    public $users = [];

    public function render()
    {
        return
<<<'HTML'
<div>
    @for($i=0;$i<5;$i++)
        <div x-data="{ items: @entangle('items.keys.'.$i, 0) }">
            <div dusk="output.alpine.keys.{{ $i }}" x-text="items"></div>
        </div>
    @endfor

    <h2>Key 1</h2>
    <button dusk="addKey1" wire:click="$set('items.keys.1', 1)">Add Key 1</button>

    <h2>Key 3</h2>
    <button dusk="addKey3" wire:click="$set('items.keys.3', 3)">Add Key 3</button>

    <div dusk="output.livewire">Keys: {{ json_encode($items) }}</div>

    <h2>Users</h2>
    <div x-data="{ users: @entangle('users.0', {{ json_encode(['name' => 'Caleb']) }})}">
        <div dusk="output.alpine.users.0" x-text="users.name"></div>
    </div>

    <button dusk="addUser" wire:click="$set('users.1', {{ json_encode(['name' => 'Caleb Porzio']) }})">Add user</button>

    <div dusk="output.livewire.users">{{ json_encode($users) }}</div>
</div>
HTML;
    }
}
