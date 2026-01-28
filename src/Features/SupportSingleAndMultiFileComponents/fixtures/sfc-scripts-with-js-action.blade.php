<?php

new class extends \Livewire\Component {
    public $foo = 'bar';
};
?>

<div>
    <div dusk="foo" wire:text="$js.getFoo"></div>
    <button wire:click="$js.setFoo('baz')" dusk="set-foo">Set Foo</button>
</div>

<script>
    this.$js.getFoo = () => {
        return this.foo;
    }

    this.$js.setFoo = (value) => {
        this.foo = value;
    }
</script>