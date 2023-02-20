<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;


class AnonymousClassComponentsTest extends TestCase
{
    /** @test */
    public function can_register_anonymous_class_as_component()
    {
        Livewire::component('foo', new class extends \Livewire\Component {
            public $count = 0;

            function inc()
            {
                $this->count++;
            }

            public function render() {
                return <<<'HTML'
                <div>
                    <span>Count: {{ $count }}</span>
                    <button wire:click="inc">inc</button>
                </div>
                HTML;
            }
        });

        $component = Livewire::test('foo')
            ->assertSee('Count: 0')
            ->call('inc')
            ->assertSee('Count: 1');
    }
}
