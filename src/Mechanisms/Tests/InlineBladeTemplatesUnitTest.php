<?php

namespace Livewire\Mechanisms\Tests;

use Livewire\Component;
use Livewire\Livewire;

class InlineBladeTemplatesUnitTest extends \Tests\TestCase
{
    public function test_renders_inline_blade_template()
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
