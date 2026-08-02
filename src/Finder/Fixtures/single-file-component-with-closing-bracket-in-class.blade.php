<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new
#[Layout('layouts.app')]
class extends Component
{
    public array $items = ['first', 'second'];
};
?>

<div>Finder Test Single File Component With Closing Bracket In Class</div>
