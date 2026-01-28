<?php

use Livewire\Component;

new class extends Component
{
    public $message = 'Hello World';
};
?>

<div>
    @island(lazy: true)
        @placeholder
            <div>Loading...</div>
        @endplaceholder
    @endisland

    {{ $message }}
</div>

<script>
    console.log('Hello from script');
</script>