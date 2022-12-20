<div>
    <span dusk="lastEventForParent">{{ $this->lastEvent }}</span>

    <button wire:click="$emit('foo', 'baz')" dusk="emit.baz"></button>

    <button wire:click="$emit('foo', 'bob')" dusk="emit.bob"></button>

    <button wire:click="$emitUp('foo', 'lob')" dusk="emit.lob"></button>

    <button wire:click="$emitSelf('foo', 'law')" dusk="emit.law"></button>

    <button wire:click="$emitTo('component-b', 'foo', 'blog')" dusk="emit.blog"></button>

    @livewire(LegacyTests\Browser\Events\NestedComponentA::class)
    @livewire(LegacyTests\Browser\Events\NestedComponentB::class)
</div>
