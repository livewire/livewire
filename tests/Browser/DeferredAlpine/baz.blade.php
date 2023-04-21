<div>
  <!-- A component that entangles livewire immediately -->
  <div x-data="{baz: $wire.entangle('baz')}">
    <span x-text="baz" dusk="bazOutput"></span>
    <input x-model="baz" dusk="bazModelInput" />
    <input wire:model="baz" dusk="bazWireInput" />
    <button @click="baz += 1" dusk="bazModelButton">baz += 1</button>
    <button @click="$wire.set('baz', baz + 1)" dusk="bazWireButton">$wire.set('baz', baz + 1)</button>
  </div>
</div>