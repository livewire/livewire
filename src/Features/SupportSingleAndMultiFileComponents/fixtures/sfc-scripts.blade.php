<?php

new class extends Livewire\Component {
    public int $count = 1;

    public function increment()
    {
        $this->count++;
    }
};
?>

<div>
    <div dusk="foo">bar</div>
</div>

<script>
    this.$el.querySelector('[dusk="foo"]').textContent = 'baz';
</script>