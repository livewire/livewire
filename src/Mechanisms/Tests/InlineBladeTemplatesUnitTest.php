<?php

namespace Livewire\Mechanisms\Tests;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class InlineBladeTemplatesUnitTest extends \Tests\TestCase
{
    #[Test]
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
