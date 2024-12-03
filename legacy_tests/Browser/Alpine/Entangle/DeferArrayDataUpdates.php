<?php

namespace LegacyTests\Browser\Alpine\Entangle;

use Livewire\Component as BaseComponent;

class DeferArrayDataUpdates extends BaseComponent
{
    public $testing;

    public $dataArray = ['role' => 'guest'];

    public function submit()
    {
        $this->reset('dataArray');
    }

    public function render()
    {
        return <<<'HTML'
<div x-data="{ alpineRole:  @entangle('dataArray.role') }">
    <select x-model="alpineRole" dusk="role-select">
        <option value="guest">Guest</option>
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>

    <p>Alpine: <span dusk="output.alpine" x-text="alpineRole"></span></p>

    <p>Livewire: <span dusk="output.livewire">{{ $dataArray['role'] }}</span></p>

    <button wire:click.prevent="submit" dusk="submit">Submit</button>
</div>
HTML;
    }
}
