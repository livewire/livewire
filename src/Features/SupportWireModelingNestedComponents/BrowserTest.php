<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_()
    {
        $this->markTestSkipped();

        Livewire::visit([
            new class extends \Livewire\Component {
                public $foo = 0;

                public function render() { return <<<'HTML'
                <div>
                    <span>Parent: {{ $foo }}</span>

                    <livewire wire:model="foo" />
                </div>
                HTML; }
            },
            'child' => new class extends \Livewire\Component {
                #[Modelable]
                public $bar;

                public function render() { return <<<'HTML'
                <div>
                    <span>Child: {{ $foo }}</span>
                </div>
                HTML; }
            },
        ])
        ->tinker()
        ;
    }
}
