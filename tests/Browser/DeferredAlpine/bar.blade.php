<div>
  <!-- A component that references livewire but doesn't entangle -->
  <div x-data>
    <span x-text="$wire.bar" dusk="barOutput"></span>
    <button @click="$wire.incrementBar()" dusk="barButton">$wire.increment()</button>
  </div>
</div>
