<?php

namespace Tests;

use Livewire\Component;
use Livewire\Livewire;

class InlineBladeTemplatesTest extends TestCase
{
    /** @test */
    public function renders_inline_blade_template()
    {
        if (! Livewire::isLaravel7()) {
            $this->expectException(\Exception::class);
        }

        Livewire::test(ComponentWithInlineBladeTemplate::class)
            ->assertSee('foo');
    }
}

class ComponentWithInlineBladeTemplate extends Component
{
    public $name = 'foo';

    public function render()
    {
        return <<<'blade'
            <div>
                <span>{{ $name }}</span>
            </div>
blade;
    }
}
