<?php

namespace LegacyTests\Browser\DataBinding\Lazy;

use Livewire\Component as BaseComponent;

class LazyInputsWithUpdatesDisplayedComponent extends BaseComponent
{
    public $name;
    public $description;

    public $is_active = false;

    public $updates = [];

    public function updated()
    {
        $this->updateUpdates();
    }

    public function submit()
    {
        $this->updateUpdates();
    }

    function updateUpdates()
    {
        // To keep the test from V2, I'm going to massage the V3 schema update data
        // back into the V2 schema here...
        $this->updates = [];

        foreach (request('components.0.updates') as $key => $value) {
            $this->updates[] = ['type' => 'syncInput', 'payload' => ['name' => $key]];
        }

        foreach (request('components.0.calls') as $call) {
            $this->updates[] = ['type' => 'callMethod', 'payload' => ['method' => $call['method']]];
        }
    }

    public function render()
    {
        return
<<<'HTML'
<div>
    <input dusk="name" wire:model.lazy="name">
    <input dusk="description" wire:model.lazy="description">

    <input dusk="is_active" type="checkbox" wire:model.live="is_active">

    <div dusk="totalNumberUpdates">{{ count($updates) }}</div>

    <div dusk="updatesList">
        @foreach($updates as $update)
            <div>
                @if($update['type'] == 'syncInput')
                    {{ $update['type'] . ' - ' . $update['payload']['name'] }}
                @elseif($update['type'] == 'callMethod')
                    {{ $update['type'] . ' - ' . $update['payload']['method'] }}
                @endif
            </div>
        @endforeach
    </div>

    <button dusk="submit" type="button" wire:click="submit">Submit</button>
</div>
HTML;
    }
}
