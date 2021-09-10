<?php

namespace Tests\Browser\Nesting;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class, '?showChild=true')
                // click inside nested component is assigned to nested component
                ->waitForLivewire()->click('@button.nested')
                ->assertSeeIn('@output.nested', 'foo')
                ->waitForLivewire()->click('@button.toggleChild')
                ->refresh()->pause(500)

                // added component gets initialized
                ->waitForLivewire()->click('@button.toggleChild')
                ->waitForLivewire()->click('@button.nested')
                ->assertSeeIn('@output.nested', 'foo')

                // can switch components
                ->waitForLivewire()->click('@button.changeKey')
                ->assertDontSeeIn('@output.nested', 'foo')
                ->waitForLivewire()->click('@button.nested')
                ->assertSeeIn('@output.nested', 'foo')
            ;
        });
    }

    /** @test */
    public function it_returns_the_render_context_back_to_the_parent_component_after_sub_component_is_rendered()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, RenderContextComponent::class)
                ->assertSeeIn('@output.blade-component1', 'Blade 1')
                ->assertSeeIn('@output.blade-component2', 'Blade 2')
                ->assertSeeIn('@output.nested', 'Sub render')
                ->assertSeeIn('@output.blade-component3', 'Blade 3')
            ;
        });
    }

    /** @test */
    public function it_renders_a_list_and_an_doesnt_error_when_the_list_changes()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, ParentWithValuesComponent::class)
                ->assertSeeIn('@values', 'Value 1')
                ->assertSeeIn('@values', 'Value 2')
                ->assertSeeIn('@values', 'Value 3')
                ->assertSeeIn('@values', 'Value 4')
                ->assertSeeIn('@values', 'Value 5')

                // For some reason without this pause the test only failed sometimes
                ->pause(500)

                ->waitForLivewire()->click('@change-button')
                ->assertDontSeeIn('@values', 'Value 1')
                ->assertSeeIn('@values', 'Value 2')
                ->assertSeeIn('@values', 'Value 3')
                ->assertSeeIn('@values', 'Value 4')
                ->assertSeeIn('@values', 'Value 5')

                ->waitForLivewire()->click('@change-button')
                ->assertDontSeeIn('@values', 'Value 1')
                ->assertDontSeeIn('@values', 'Value 2')
                ->assertSeeIn('@values', 'Value 3')
                ->assertSeeIn('@values', 'Value 4')
                ->assertSeeIn('@values', 'Value 5')
            ;
        });
    }
}
