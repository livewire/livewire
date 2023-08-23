<?php

namespace LegacyTests\Browser\SupportStringables;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class Test extends TestCase
{
    public function test_stringable_support()
    {
        Livewire::test(new class extends \Livewire\Component {
            public $string;

            public function mount()
            {
                $this->string = Str::of('Be excellent to each other');
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        {{ $string }}
                    </div>
                HTML;
            }
        })
            ->assertSee('Be excellent to each other')
        ;
    }
}
