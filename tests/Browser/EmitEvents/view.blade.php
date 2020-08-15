<div>
    <span dusk="lastEventForParent">{{ $this->lastEvent }}</span>

    @livewire(Tests\Browser\EmitEvents\NestedComponentA::class)
    @livewire(Tests\Browser\EmitEvents\NestedComponentB::class)
</div>
