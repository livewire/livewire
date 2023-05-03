<div>
    <span dusk="lastEventForParent">{{ $this->lastEvent }}</span>

    <button wire:click="$dispatch('foo', 'baz')" dusk="dispatch.baz"></button>

    <button wire:click="$dispatch('foo', 'bob')" dusk="dispatch.bob"></button>

    <button wire:click="$dispatchUp('foo', 'lob')" dusk="dispatch.lob"></button>

    <button wire:click="$dispatchSelf('foo', 'law')" dusk="dispatch.law"></button>

    <button wire:click="$dispatchTo('component-b', 'foo', 'blog')" dusk="dispatch.blog"></button>

    @livewire(LegacyTests\Browser\Events\NestedComponentA::class)
    @livewire(LegacyTests\Browser\Events\NestedComponentB::class)
</div>
