<div>
    <span dusk="lastEventForParent">{{ $this->lastEvent }}</span>

    <button wire:click="$emit('foo', 'baz')" dusk="emit.baz"></button>

    <button wire:click="$emit('foo', 'bob')" dusk="emit.bob"></button>

    <button wire:click="$emitUp('foo', 'lob')" dusk="emit.lob"></button>

    <button wire:click="$emitSelf('foo', 'law')" dusk="emit.law"></button>

    <button wire:click="$emitTo('tests.browser.events.nested-component-b', 'foo', 'blog')" dusk="emit.blog"></button>

    @livewire(Tests\Browser\Events\NestedComponentA::class)
    @livewire(Tests\Browser\Events\NestedComponentB::class)
</div>
