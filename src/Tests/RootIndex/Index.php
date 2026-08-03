<?php

namespace Livewire\Tests\RootIndex;

use Livewire\Component;

class Index extends Component
{
    public int $count = 1;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                Count: {{ $count }}
            </div>
        HTML;
    }
}
