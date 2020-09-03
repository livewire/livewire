<div>
    <div>
        Parent:
        <span dusk="parent-output">{{ $parent->value }}</span>
        <input wire:model="parent.value" type="text" dusk="parent-input">
    </div>

    <div>
        Child:
        <span dusk="child-output">{{ $child->value }}</span>
        <input wire:model="child.value" type="text" dusk="child-input">
    </div>

    <button wire:click="$set('showNestedComponent', true)" dusk="show-nested">Show Nested Component</button>

    @if ($showNestedComponent)
        @livewire(\Tests\Browser\SyncHistory\NestedComponent::class)
    @endif
</div>
