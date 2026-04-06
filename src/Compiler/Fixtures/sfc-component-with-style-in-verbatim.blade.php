<?php

use Livewire\Component;

new class extends Component
{
    public $message = 'Hello World';
};
?>

<div>{{ $message }}</div>

@verbatim
```html
<style>
    .example { color: red; }
</style>
```
@endverbatim
