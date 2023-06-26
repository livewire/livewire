<?php

namespace Livewire\Features\SupportProps;

use Livewire\Livewire;
use Livewire\Component;
use ReflectionObject;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    function can_pass_prop_to_child_component()
    {
        Livewire::test([new class extends Component {
            public $foo = 'bar';

            public function render() {
                return '<livewire:child :oof="$foo" />';
            }
        }, 'child' => new class extends Component {
            #[Prop]
            public $oof;

            public function render() {
                return '<div>{{ $oof }}</div>';
            }

        }])
        ->assertSee('bar');
    }

    /** @test */
    function can_change_reactive_prop_in_child_component()
    {
        $component = Livewire::test([new class extends Component {
            public $todos = [];

            public function render() {
                return '<livewire:child :todos="$todos" />';
            }
        }, 'child' => new class extends Component {
            #[Prop(reactive: true)]
            public $todos;

            public function render() {
                return '<div>Count: {{ count($todos) }}.</div>';
            }
        }]);

        $component->assertSee('Count: 0.');

        $component->set('todos', ['todo 1']);
        $component->assertSee('Count: 1.');

        $component->set('todos', ['todo 1', 'todo 2', 'todo 3']);
        $component->assertSee('Count: 3.');
    }
}

