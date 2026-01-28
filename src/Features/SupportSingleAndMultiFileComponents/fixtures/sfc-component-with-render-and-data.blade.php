<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view([
            'message' => 'Hello World',
        ]);
    }
};
?>

<div>Message: {{ $message }}</div>
