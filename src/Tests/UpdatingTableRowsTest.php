<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class UpdatingTableRowsTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function component_renders_table_rows_and_updates_properly()
    {
        Livewire::visit([new class extends Component {
            public function render() {
                return <<<'HTML'
                    <table>
                        <tbody>
                            <livewire:child />
                        </tbody>
                    </table>
                HTML;
            }
        },
        'child' => new class extends Component {
            public int $counter = 0;

            public function increment()
            {
                $this->counter++;
            }

            public function render()
            {
                return <<<'HTML'
                    <tr dusk="table-row">
                        <td>
                            <button type="button" wire:click="increment" dusk="increment">+</button>
                        </td>
                        <td>
                            <input wire:model="counter" dusk="counter">
                        </td>
                    </tr>
                HTML;
            }
        }])
            ->assertVisible('@table-row')
            ->assertInputValue('@counter', '0')
            ->click('@increment')
            ->waitForLivewire()
            ->assertVisible('@table-row')
            ->assertInputValue('@counter', '1')
        ;
    }
}
