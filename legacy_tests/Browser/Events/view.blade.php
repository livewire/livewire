<div>
    <span dusk="lastEventForParent">{{ $this->lastEvent }}</span>

    <button wire:click="$dispatch('foo', { value: 'baz' })" dusk="dispatch.baz"></button>

    <button wire:click="$dispatch('foo', { value: 'bob' })" dusk="dispatch.bob"></button>

    <button wire:click="$dispatchSelf('foo', { value: 'law' })" dusk="dispatch.law"></button>

    <button wire:click="$dispatchTo('component-b', 'foo', { value: 'blog' })" dusk="dispatch.blog"></button>

    @livewire(LegacyTests\Browser\Events\NestedComponentA::class)
    @livewire(LegacyTests\Browser\Events\NestedComponentB::class)
</div>
