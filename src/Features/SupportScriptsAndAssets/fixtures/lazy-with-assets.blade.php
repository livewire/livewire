<?php

use Livewire\Component;
use Livewire\Attributes\Lazy;

new #[Lazy] class extends Component
{
    //
};
?>

<div>
    <input type="text" data-picker>
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
@endassets

<script>
    window.datePicker = new Pikaday({ field: $wire.$el.querySelector('[data-picker]') });
</script>
