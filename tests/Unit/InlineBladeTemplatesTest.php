<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class InlineBladeTemplatesTest extends TestCase
{
    /** @test */
    public function renders_inline_blade_template()
    {
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
