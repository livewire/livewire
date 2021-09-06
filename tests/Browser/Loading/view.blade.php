<div>
    <button wire:click="$refresh" dusk="button">Load</button>
    <button wire:click="throwError" dusk="error-button">Throw Error</button>

    <h1 wire:loading dusk="show">Loading...</h1>
    <h1 wire:loading.remove dusk="hide">Loading...</h1>

    <h1 wire:loading.class="foo bar" dusk="add-class">Loading...</h1>
    <h1 wire:loading.class.remove="foo" dusk="remove-class" class="foo">Loading...</h1>

    <h1 wire:loading.attr="disabled" dusk="add-attr">Loading...</h1>
    <h1 wire:loading.attr.remove="disabled" dusk="remove-attr" disabled>Loading...</h1>

    <h1 wire:loading.class="foo" wire:loading.attr="disabled" dusk="add-both">Loading...</h1>
    <h1 wire:loading.class.remove="foo" wire:loading.attr.remove="disabled" dusk="remove-both" disabled  class="foo">Loading...</h1>

    <h1 wire:loading wire:target="foo" dusk="targeting">Loading...</h1>
    <h1 wire:loading wire:target="foo, bar" dusk="targeting-both">Loading...</h1>
    <h1 wire:loading wire:target="foo('bar')" dusk="targeting-param">Loading...</h1>

    <h1 wire:loading.delay dusk="show-w-delay">Loading with delay...</h1>

    <button wire:click="foo" dusk="target-button">targeted button</button>
    <button wire:click="foo('bar')" dusk="target-button-w-param">targeted button with param</button>

    <button wire:click="bar" wire:loading.class="foo" dusk="self-target-button">self-targeted button</button>

    <input type="checkbox" wire:model="baz" wire:loading.class="foo" dusk="self-target-model">self-targeted-model input</input>

    <h1 wire:loading wire:target='bob' dusk="target-top-level-property">Loading with top level property target</h1>

    <input type="name" wire:model='bob.name' dusk="nested-property-input" />Nested property input
</div>
