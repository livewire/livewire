<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class AlpineEntangleLiveBrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_hydrate_component_with_entangle_live_array_value()
    {
        $foo = Livewire::visit(new class extends Component {
            public $tasks = [['id' => '123']];

            function doNothing() {}

            function render() {
                return <<<'HTML'
                <div x-data="{
                    tasks: @entangle('tasks').live,
                    removeTask(idToDelete) {
                        this.tasks = this.tasks.filter(task => task.id !== idToDelete);
                    }
                    }">
                    <div>
                        <template x-for="(task, index) in tasks" key="task.id">
                            <div>
                                <span x-text="task.id"></span>
                                <button dusk="remove" type="button" x-on:click="removeTask(task.id)">Remove task</button>
                                <span>I can see you</span>
                            </div>
                        </template>
                        <button wire:click="doNothing" dusk="doNothing">Nothing</button>
                    </div>
                </div>
                HTML;
            }
        })
            ->assertSee('I can see you')
            ->waitForLivewire()->click('@remove')
            ->waitForLivewire()->click('@doNothing')
            ->assertDontSee('I can see you');
    }
}