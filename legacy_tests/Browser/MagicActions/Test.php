<?php

namespace LegacyTests\Browser\MagicActions;

use Livewire\Livewire;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    public function test_magic_toggle_can_toggle_properties()
    {
        Livewire::visit(Component::class)
            //Toggle boolean property
            ->assertSeeIn('@output', 'false')
            ->waitForLivewire()->click('@toggle')
            ->assertSeeIn('@output', 'true')
            ->waitForLivewire()->click('@toggle')
            ->assertSeeIn('@output', 'false')

            //Toggle nested boolean property
            ->assertSeeIn('@outputNested', 'false')
            ->waitForLivewire()->click('@toggleNested')
            ->assertSeeIn('@outputNested', 'true')
            ->waitForLivewire()->click('@toggleNested')
            ->assertSeeIn('@outputNested', 'false')
        ;
    }

    public function test_magic_event_works()
    {
        Livewire::visit(Component::class)
            ->assertDontSeeIn('@outputEvent', 'baz')
            ->waitForLivewire()->click('@fillBar')
            ->assertSeeIn('@outputEvent', 'baz')
        ;
    }
}

class Component extends \Livewire\Component
{
    public $active = false;
    public $foo = ['bar' => ['baz' => false]];
    public $bar = '';

    public function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                <div dusk="output">{{ $active ? "true" : "false" }}</div>
                <button wire:click="$toggle('active')" dusk="toggle">Toggle Property</button>

                <div dusk="outputNested">{{ $foo['bar']['baz'] ? "true" : "false" }}</div>
                <button wire:click="$toggle('foo.bar.baz')" dusk="toggleNested">Toggle Nested</button>

                <div dusk="outputEvent">{{ $bar }}</div>
                <div wire:click="setBar($event.target.id)" id="baz" dusk="fillBar">Click me</div>
            </div>
        HTML;
    }
}
