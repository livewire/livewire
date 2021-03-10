<?php

namespace Tests\Browser\Alpine\Emit;

use Livewire\Component as BaseComponent;

class EmitComponent extends BaseComponent
{
    public $emit = false;
    public $emitUp = false;
    public $emitTo = false;
    public $emitSelf = false;

    protected $listeners = ['emit' => 'emitHandler'];

    public function emitHandler($eventName)
    {
        $this->$eventName = true;
    }

    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-data>
        <button dusk="emit" @click="$wire.emit('emit', 'emit')">Emit</button>

        @if ($emit)
            emit worked!
        @endif
    </div>

    <div x-data>
        <button dusk="emitSelf" @click="$wire.emitSelf('emit', 'emitSelf')">Emit Self</button>

        @if ($emitSelf)
            emit self worked!
        @endif
    </div>

    @livewire(Tests\Browser\Alpine\Emit\EmitNestedComponent::class)

    @if ($emitUp)
        emit up worked!
    @endif

    @if ($emitTo)
        emit to worked!
    @endif
</div>
HTML;
    }
}
