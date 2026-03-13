<?php

use Livewire\Component;

new class extends Component
{
    public $message = 'Hello World';
};
?>

<div>
    {{ $message }}

    <div>
        <script>
            console.log('This should NOT be extracted - it is nested inside div');
        </script>
    </div>
</div>
