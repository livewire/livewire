<?php

namespace LegacyTests\Browser\SupportEnums;

use LegacyTests\TestEnum;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class Test extends TestCase
{
    public function test()
    {
        Livewire::test(new class extends Component {
            public $enum;

            public function mount()
            {
                $this->enum = TestEnum::TEST;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        {{ $enum->value }}
                    </div>
                HTML;
            }
        })
            ->assertSee('Be excellent to each other')
        ;
    }
}
