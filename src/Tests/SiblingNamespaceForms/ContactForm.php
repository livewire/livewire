<?php

namespace Livewire\Tests\SiblingNamespaceForms;

use Livewire\Component;

class ContactForm extends Component
{
    public function render()
    {
        return <<<'HTML'
            <div>outside the configured namespace</div>
        HTML;
    }
}
