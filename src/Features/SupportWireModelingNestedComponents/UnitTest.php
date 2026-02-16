<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use Livewire\Component;
use Livewire\Exceptions\ModelableRootHasWireModelException;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_modelable_throws_when_root_element_has_wire_model()
    {
        $this->expectException(ModelableRootHasWireModelException::class);

        Livewire::test([
            new class extends Component {
                public $foo = '';

                public function render() { return <<<'HTML'
                <div>
                    <livewire:child wire:model="foo" />
                </div>
                HTML; }
            },
            'child' => new class extends Component {
                #[BaseModelable]
                public $value = '';

                public function render() { return <<<'HTML'
                <select wire:model="value">
                    <option value="a">A</option>
                    <option value="b">B</option>
                </select>
                HTML; }
            },
        ]);
    }

    public function test_modelable_works_when_input_is_wrapped_in_div()
    {
        Livewire::test([
            new class extends Component {
                public $foo = '';

                public function render() { return <<<'HTML'
                <div>
                    <livewire:child wire:model="foo" />
                </div>
                HTML; }
            },
            'child' => new class extends Component {
                #[BaseModelable]
                public $value = '';

                public function render() { return <<<'HTML'
                <div>
                    <select wire:model="value">
                        <option value="a">A</option>
                        <option value="b">B</option>
                    </select>
                </div>
                HTML; }
            },
        ])->assertSee('A');
    }
}
