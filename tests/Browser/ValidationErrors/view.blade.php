<div x-data>
    <span v-if="$wire.errors.length" dusk="errors"></span>
    <input wire:model="foo" dusk="foo">
    <input type="submit" dusk="submit">
</div>
