<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Livewire;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\TestComponent;

class TestableLivewireCanBeInvaded extends \Tests\TestCase
{
    function test_can_invade_protected_properties()
    {
        $component = Livewire::test(new class extends TestComponent {
            protected string $foo = 'bar';
        });

        PHPUnit::assertEquals('bar', $component->invade()->foo);
    }

    function test_can_invade_protected_functions()
    {
        $component = Livewire::test(new class extends TestComponent {
            protected function foo() : string {
                return 'bar';
            }
        });

        PHPUnit::assertEquals('bar', $component->invade()->foo());
    }

    function test_can_invade_private_properties()
    {
        $component = Livewire::test(new class extends TestComponent {
            private string $foo = 'bar';
        });

        PHPUnit::assertEquals('bar', $component->invade()->foo);
    }

    function test_can_invade_private_functions()
    {
        $component = Livewire::test(new class extends TestComponent {
            private function foo() : string {
                return 'bar';
            }
        });

        PHPUnit::assertEquals('bar', $component->invade()->foo());
    }
}
