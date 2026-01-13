<?php

use Livewire\Component;

new class extends Component
{
    public $message = 'Hello World';
};
?>

<div>{{ $message }}</div>

@script
<script>
    console.log('Hello from script');
</script>
@endscript