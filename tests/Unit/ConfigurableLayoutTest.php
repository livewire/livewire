<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Tests\AppLayout;

class ConfigurableLayoutTest extends TestCase
{
    /** @test */
    public function uses_standard_app_layout_by_default()
    {
        Route::get('/configurable-layout', ComponentForConfigurableLayoutTest::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertDontSee('baz');
    }

    /** @test */
    public function can_configure_a_default_layout()
    {
        config()->set('livewire.layout', 'layouts.app-with-baz-hardcoded');

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTest::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertSee('baz');
    }

    /** @test */
    public function can_configure_the_default_layout_to_a_class_based_component_layout()
    {
        config()->set('livewire.layout', AppLayout::class);

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTest::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertSee('bar')
            ->assertDontSee('baz');
    }

    /** @test */
    public function can_show_params_with_a_configured_class_based_component_layout()
    {
        config()->set('livewire.layout', AppLayout::class);

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTestWithCustomParams::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertSee('bar')
            ->assertSee('baz');
    }

    /** @test */
    public function can_set_custom_slot_for_a_configured_class_based_component_layout()
    {
        config()->set('livewire.layout', AppLayout::class);

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTestWithCustomSlot::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertSee('bar');
    }

    /** @test */
    public function can_configure_the_default_layout_to_an_anonymous_component_layout()
    {
        config()->set('livewire.layout', 'layouts.app-anonymous-component');

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTest::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertSee('bar')
            ->assertDontSee('baz');
    }

    /** @test */
    public function can_show_params_with_a_configured_anonymous_component_layout()
    {
        config()->set('livewire.layout', 'layouts.app-anonymous-component');

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTestWithCustomParams::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertSee('bar')
            ->assertSee('baz');
    }

    /** @test */
    public function can_set_custom_slot_for_a_configured_anonymous_component_layout()
    {
        config()->set('livewire.layout', 'layouts.app-anonymous-component');

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTestWithCustomSlot::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertSee('bar');
    }

    /** @test */
    public function can_show_params_with_a_configured_anonymous_component_layout_that_has_a_required_prop()
    {
        config()->set('livewire.layout', 'layouts.app-anonymous-component-with-required-prop');

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTestWithCustomParams::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertSee('bar')
            ->assertSee('baz');
    }

    /** @test */
    public function can_override_optional_props_with_a_configured_anonymous_component_layout()
    {
        config()->set('livewire.layout', 'layouts.app-anonymous-component');

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTestWithCustomFooParam::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertDontSee('bar')
            ->assertSee('baz');
    }

    /** @test */
    public function can_pass_attributes_to_a_configured_class_based_component_layout()
    {
        config()->set('livewire.layout', AppLayout::class);

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTestWithCustomAttributes::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('class="foo"', false)
            ->assertSee('id="foo"', false);
    }

    /** @test */
    public function can_pass_attributes_to_a_configured_anonymous_component_layout()
    {
        config()->set('livewire.layout', 'layouts.app-anonymous-component');

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTestWithCustomAttributes::class);

        $this
            ->get('/configurable-layout')
            ->assertSee('class="foo"', false);
    }
}

class ComponentForConfigurableLayoutTest extends Component
{
    public $name = 'foo';

    public function render()
    {
        return view('show-name');
    }
}

class ComponentForConfigurableLayoutTestWithCustomParams extends Component
{
    public $name = 'foo';

    public function render()
    {
        return view('show-name')->layoutData([
            'bar' => 'baz',
        ]);
    }
}

class ComponentForConfigurableLayoutTestWithCustomSlot extends Component
{
    public $name = 'foo';

    public function render()
    {
        return view('show-name')->slot('bar');
    }
}

class ComponentForConfigurableLayoutTestWithCustomFooParam extends Component
{
    public $name = 'foo';

    public function render()
    {
        return view('show-name')->layoutData([
            'foo' => 'baz',
        ]);
    }
}

class ComponentForConfigurableLayoutTestWithCustomAttributes extends Component
{
    public $name = 'foo';

    public function render()
    {
        return view('show-name')->layoutData([
            'attributes' => [
                'class' => 'foo',
            ]
        ]);
    }
}