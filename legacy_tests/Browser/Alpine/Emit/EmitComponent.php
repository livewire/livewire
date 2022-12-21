<?php

namespace LegacyTests\Browser\Alpine\Emit;

use Livewire\Component as BaseComponent;

class EmitComponent extends BaseComponent
{
    public $events = [
        'emit' => false,
        'emitUp' => false,
        'emitTo' => false,
        'emitSelf' => false,
    ];

    protected $listeners = ['emit' => 'emitHandler'];

    public function emitHandler($eventName)
    {
        $this->events[$eventName] = true;
    }

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data>
        <button dusk="emit" @click="$wire.emit('emit', 'emit')">Emit</button>

        @if ($events['emit'])
            emit worked!
        @endif
    </div>

    <div x-data>
        <button dusk="emitSelf" @click="$wire.emitSelf('emit', 'emitSelf')">Emit Self</button>

        @if ($events['emitSelf'])
            emit self worked!
        @endif
    </div>

    @livewire(LegacyTests\Browser\Alpine\Emit\EmitNestedComponent::class)

    @if ($events['emitUp'])
        emit up worked!
    @endif

    @if ($events['emitTo'])
        emit to worked!
    @endif
</div>
HTML;
    }
}
