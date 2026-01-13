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
