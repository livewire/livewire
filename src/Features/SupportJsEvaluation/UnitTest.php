<?php

namespace Livewire\Features\SupportJsEvaluation;

use Livewire\Livewire;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_assert_js()
    {
        $component = Livewire::test(new class extends TestComponent {
            public function someMethod()
            {
                $this->js("alert('do something');");
            }
        });

        try {
            $component->assertJs("alert('do something');");
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringContainsString('Failed asserting that dispatched JS matches expected JS.', $e->getMessage());
        }

        $component->call('someMethod')->assertJs("alert('do something');");

        try {
            $component->call('someMethod')->assertJs("alert('do something else');");
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertStringContainsString('Failed asserting that dispatched JS matches expected JS.', $e->getMessage());
        }
    }

    public function test_assert_js_multiline()
    {
        $component = Livewire::test(new class extends TestComponent {
            public function someMethod()
            {
                $this->js("alert('
                    do something
                ');");
            }
        });

        $component->call('someMethod')->assertJs("alert('
                            do something
        ');");

        $component->call('someMethod')->assertJs("alert('do something');");
    }
}
