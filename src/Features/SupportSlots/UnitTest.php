<?php

namespace Livewire\Features\SupportSlots;

use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_named_slot()
    {
        Livewire::test([
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        <livewire:child>
                            <livewire:slot name="header">
                                Header
                            </livewire:slot>

                            Hello world
                        </livewire:child>
                    </div>
                HTML; }
            },
            'child' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        @if ($slots->has('header'))
                            {{ $slots->get('header') }}
                        @endif

                        {{ $slots['default'] }}
                    </div>
                HTML; }
            },
        ])
        ->assertSee('Header')
        ->assertSee('Hello world')
        ;
    }

    public function test_multiple_named_slots()
    {
        Livewire::test([
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        <livewire:child>
                            <livewire:slot name="first">
                                First slot content
                            </livewire:slot>
                            <livewire:slot name="second">
                                Second slot content
                            </livewire:slot>
                        </livewire:child>
                    </div>
                HTML; }
            },
            'child' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        @if ($slots->has('first'))
                            <div id="first">{{ $slots['first'] }}</div>
                        @endif

                        @if ($slots->has('second'))
                            <div id="second">{{ $slots['second'] }}</div>
                        @endif
                    </div>
                HTML; }
            },
        ])
        ->assertSee('First slot content')
        ->assertSee('Second slot content')
        ;
    }

    public function test_single_named_slot_with_nested_livewire_component()
    {
        Livewire::test([
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        <livewire:child>
                            <livewire:slot name="actions">
                                <livewire:nested />
                            </livewire:slot>
                        </livewire:child>
                    </div>
                HTML; }
            },
            'child' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        @if ($slots->has('actions'))
                            <div id="actions">{{ $slots['actions'] }}</div>
                        @endif
                    </div>
                HTML; }
            },
            'nested' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>Nested content</div>
                HTML; }
            },
        ])
        ->assertSee('Nested content')
        ;
    }

    public function test_multiple_named_slots_with_nested_livewire_component()
    {
        Livewire::test([
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        <livewire:child>
                            <livewire:slot name="first">
                                First slot content
                            </livewire:slot>
                            <livewire:slot name="second">
                                <livewire:nested />
                            </livewire:slot>
                        </livewire:child>
                    </div>
                HTML; }
            },
            'child' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>
                        @if ($slots->has('first'))
                            <div id="first">{{ $slots['first'] }}</div>
                        @endif

                        @if ($slots->has('second'))
                            <div id="second">{{ $slots['second'] }}</div>
                        @endif
                    </div>
                HTML; }
            },
            'nested' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                    <div>Nested component content</div>
                HTML; }
            },
        ])
        ->assertSee('First slot content')
        ->assertSee('Nested component content')
        ;
    }

    public function test_slot_with_short_attribute_syntax()
    {
        Livewire::test([
            new class extends \Livewire\Component {
                public $foo = 'foo';

                public function render() { return <<<'HTML'
                    <div>
                        @foreach (range(1, 3) as $i)
                            <livewire:child :$foo>Hello world</livewire:child>
                        @endforeach
                    </div>
                HTML; }
            },
            'child' => new class extends \Livewire\Component {
                public $foo;

                public function render() { return <<<'HTML'
                    <div {{ $attributes->class('child') }}>{{ $slot }}</div>
                HTML; }
            },
        ])
        ->assertSee([
            'Hello world',
        ]);
    }
}
