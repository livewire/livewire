<?php

use Livewire\Component;

new class extends Component
{
    public $message = 'Hello World';
};
?>

<div>
    {{ $message }}

    <script>
        console.log('This should NOT be extracted - it is nested inside div');
    </script>
</div>

<script>
    console.log('This SHOULD be extracted - it is at root level');
</script>
