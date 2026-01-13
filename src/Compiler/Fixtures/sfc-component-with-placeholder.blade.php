<?php

use Livewire\Component;

new class extends Component
{
    public $message = 'Hello World';
};
?>

@placeholder
    <div>Loading...</div>
@endplaceholder

<div>{{ $message }}</div>

<script>
    console.log('Hello from script');
</script>