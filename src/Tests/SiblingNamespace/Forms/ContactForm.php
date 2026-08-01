<?php

namespace Livewire\Tests\SiblingNamespace\Forms;

use Livewire\Component;

class ContactForm extends Component
{
    public function render()
    {
        return <<<'HTML'
            <div>inside the configured namespace</div>
        HTML;
    }
}
