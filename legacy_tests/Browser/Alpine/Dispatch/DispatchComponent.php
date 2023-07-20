<?php

namespace LegacyTests\Browser\Alpine\Dispatch;

use Livewire\Component as BaseComponent;

class DispatchComponent extends BaseComponent
{
    public $events = [
        'dispatch' => false,
        'dispatchUp' => false,
        'dispatchTo' => false,
        'dispatchSelf' => false,
    ];

    protected $listeners = ['dispatch' => 'dispatchHandler'];

    public function dispatchHandler($name)
    {
        $this->events[$name] = true;
    }

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data>
        <button dusk="dispatch" @click="$wire.dispatch('dispatch', { name: 'dispatch' })">Dispatch</button>

        @if ($events['dispatch'])
            Dispatch worked!
        @endif
    </div>

    <div x-data>
        <button dusk="dispatchSelf" @click="$wire.dispatchSelf('dispatch', { name: 'dispatchSelf' })">Dispatch Self</button>

        @if ($events['dispatchSelf'])
            Dispatch self worked!
        @endif
    </div>

    @livewire(LegacyTests\Browser\Alpine\Dispatch\DispatchNestedComponent::class)

    @if ($events['dispatchUp'])
        Dispatch up worked!
    @endif

    @if ($events['dispatchTo'])
        Dispatch to worked!
    @endif
</div>
HTML;
    }
}
