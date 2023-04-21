<div>
  <!-- A component that doesn't attempt to touch livewire until interaction -->
  <div x-data="{foo: 'Hello Livewire'}">
    <input x-model="foo" dusk="fooInput" />

    <button @click="foo = $wire.foo" dusk="fooButton">foo = $wire.foo</button>
  </div>
</div>