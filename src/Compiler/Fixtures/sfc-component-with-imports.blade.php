<?php

use Livewire\Component;

new class extends Component
{
    public $message = 'Hello World';
};
?>

<div>{{ $message }}</div>

<script>
import { Alpine } from 'alpinejs'
import { debounce } from './utils'

console.log('Component initialized');
Alpine.data('myComponent', () => ({
    init() {
        debounce(() => console.log('Debounced'), 100);
    }
}));
</script>
