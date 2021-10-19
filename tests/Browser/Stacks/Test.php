<?php

namespace Tests\Browser\Stacks;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function conditionally_loaded_component_can_push_and_preppend_to_stack()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertScript('JSON.stringify(window.stack_output)', json_encode([
                    'parent-scripts',
                ]))
                ->waitForLivewire()->click('@toggle-child')
                ->assertScript('JSON.stringify(window.stack_output)', json_encode([
                    'parent-scripts', 'child-scripts',
                ]))
                ->waitForLivewire()->click('@toggle-child')
                ->waitForLivewire()->click('@toggle-child')
                ->assertScript('JSON.stringify(window.stack_output)', json_encode([
                    'parent-scripts', 'child-scripts',
                ]))
                ->waitForLivewire()->click('@refresh-parent')
                ->assertScript('JSON.stringify(window.stack_output)', json_encode([
                    'parent-scripts', 'child-scripts',
                ]))
                ->waitForLivewire()->click('@toggle-blade-child')
                ->assertScript('JSON.stringify(window.stack_output)', json_encode([
                    'parent-scripts', 'child-scripts', 'child-blade-scripts', 'child-blade-scripts-no-once', 'child-blade-scripts-no-once',
                ]))
                ->waitForLivewire()->click('@toggle-blade-child')
                ->waitForLivewire()->click('@toggle-blade-child')
                ->waitForLivewire()->click('@refresh-child')
                ->assertScript('JSON.stringify(window.stack_output)', json_encode([
                    'parent-scripts', 'child-scripts', 'child-blade-scripts', 'child-blade-scripts-no-once', 'child-blade-scripts-no-once',
                ]))
            ;
        });
    }
}
