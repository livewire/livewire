<?php

namespace Livewire\Features\SupportIslands;

use Tests\TestCase;
use Livewire\Livewire;

class UnitTest extends TestCase
{
    public function test_render_island_directives()
    {
        $this->markTestSkipped();

        $component = Livewire::test(new class extends \Livewire\Component {
            public function render() {
                return <<<'HTML'
                <div>
                    Outside island

                    @island
                        Inside island
                    @endisland
                </div>
                HTML;
            }
        })
            ->assertDontSee('@island')
            ->assertDontSee('@endisland')
            ->assertSee('Outside island')
            ->assertSee('Inside island')
            ->assertSee('!--[if ISLAND:')
            ->assertSee('!--[if ENDISLAND:');
    }
}
