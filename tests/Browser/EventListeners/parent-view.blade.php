<div>
    <div>
        <button dusk="emitFoo" wire:click="$emit('foo', 'foo')">Foo</button>
        <button dusk="emitBar" wire:click="$emit('bar', 'bar')">Bar</button>
        <button dusk="emitBaz" wire:click="$emit('baz', 'baz')">Baz</button>
        <button dusk="emitGoo" wire:click="$emit('goo', 'goo')">Goo</button><br />
    </div>
    <h3>Parent</h3>
    <div>
        <span dusk="parent_eventCount">{{$eventCount}}</span><br />
        <span dusk="parent_lastEvent">{{$lastEvent}}</span><br />

        <button dusk="parent_removeBar" wire:click="delete(2)">Remove bar handler</button><br />
        <button dusk="parent_addGoo" wire:click="add(4, 'goo')">Add goo handler</button>
    </div>
    @livewire(Tests\Browser\EventListeners\ChildComponent::class)
</div>
