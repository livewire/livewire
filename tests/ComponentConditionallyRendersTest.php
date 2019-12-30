<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentConditionallyRendersTest extends TestCase
{
    /** @test */
    public function can_render_the_component_if_the_conditional_is_true()
    {
        $component = app(LivewireManager::class)->test(ComponentWithConditionToRender::class);

        $component->set('shouldBeRendered', true);

        $component->assertSee('Hello World');
    }

    /** @test */
    public function cant_render_the_component_if_the_conditional_is_false()
    {
        $component = app(LivewireManager::class)->test(ComponentWithConditionToRender::class);

        $component->set('shouldBeRendered', false);

        $component->assertDontSee('Hello World');
    }
}

class ComponentWithConditionToRender extends Component
{
    public $shouldBeRendered;

    public function render()
    {
        return view('conditional-render');
    }

    public function renderWhen()
    {
        return $this->shouldBeRendered;
    }
}
