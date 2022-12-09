<?php

namespace Livewire\Features\SupportPageComponents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class Test extends \Tests\TestCase
{
    /** @test */
    public function uses_standard_app_layout_by_default()
    {
        Route::get('/configurable-layout', ComponentForConfigurableLayoutTest::class);

        $this
            ->withoutExceptionHandling()
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
        config()->set('livewire.layout', \LegacyTests\AppLayout::class);

        Route::get('/configurable-layout', ComponentForConfigurableLayoutTest::class);

        $this
            ->withoutExceptionHandling()
            ->get('/configurable-layout')
            ->assertSee('foo')
            ->assertSee('bar')
            ->assertDontSee('baz');
    }

    /** @test */
    public function can_show_params_with_a_configured_class_based_component_layout()
    {
        config()->set('livewire.layout', \LegacyTests\AppLayout::class);

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
        config()->set('livewire.layout', \LegacyTests\AppLayout::class);

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
        config()->set('livewire.layout', \LegacyTests\AppLayout::class);

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

    /** @test */
    public function can_extend_a_blade_layout()
    {
        $this->withoutExceptionHandling();

        Livewire::component(ComponentWithExtendsLayout::class);

        Route::get('/foo', ComponentWithExtendsLayout::class);

        $this->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_set_custom_section()
    {
        Livewire::component(ComponentWithCustomSection::class);

        Route::get('/foo', ComponentWithCustomSection::class);

        $this->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_set_custom_layout()
    {
        Livewire::component(ComponentWithCustomLayout::class);

        Route::get('/foo', ComponentWithCustomLayout::class);

        $this->withoutExceptionHandling()->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_set_custom_slot_for_a_layout()
    {
        Livewire::component(ComponentWithCustomSlotForLayout::class);

        Route::get('/foo', ComponentWithCustomSlotForLayout::class);

        $this->withoutExceptionHandling()->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_show_params_with_a_custom_class_based_component_layout()
    {
        Livewire::component(ComponentWithClassBasedComponentLayout::class);

        Route::get('/foo', ComponentWithClassBasedComponentLayout::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('bar')
            ->assertSee('baz');
    }

    /** @test */
    public function can_show_params_set_in_the_constructor_of_a_custom_class_based_component_layout()
    {
        Livewire::component(ComponentWithClassBasedComponentLayoutAndParams::class);

        Route::get('/foo', ComponentWithClassBasedComponentLayoutAndParams::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('bar')
            ->assertSee('baz');
    }

    /** @test */
    public function can_show_attributes_with_a_custom_class_based_component_layout()
    {
        Livewire::component(ComponentWithClassBasedComponentLayoutAndAttributes::class);

        Route::get('/foo', ComponentWithClassBasedComponentLayoutAndAttributes::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('class="foo"', false);
    }

    /** @test */
    public function can_show_params_with_a_custom_anonymous_component_layout()
    {
        Livewire::component(ComponentWithAnonymousComponentLayout::class);

        Route::get('/foo', ComponentWithAnonymousComponentLayout::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('bar')
            ->assertSee('baz');
    }

    /** @test */
    public function can_show_attributes_with_a_custom_anonymous_component_layout()
    {
        Livewire::component(ComponentWithAnonymousComponentLayoutAndAttributes::class);

        Route::get('/foo', ComponentWithAnonymousComponentLayoutAndAttributes::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('class="foo"', false)
            ->assertSee('id="foo"', false);
    }

    /** @test */
    public function can_show_the_params()
    {
        Livewire::component(ComponentWithCustomParams::class);

        Route::get('/foo', ComponentWithCustomParams::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('foo');
    }

    /** @test */
    public function can_show_params_with_custom_layout()
    {
        Livewire::component(ComponentWithCustomParamsAndLayout::class);

        Route::get('/foo', ComponentWithCustomParamsAndLayout::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('livewire');
    }

    /** @test */
    public function route_supports_laravels_missing_fallback_function(): void
    {
        if (! method_exists(\Illuminate\Routing\Route::class, 'missing')) {
            $this->markTestSkipped('Need Laravel >= 8');
        }

        Route::get('awesome-js/{framework}', ComponentWithModel::class)
             ->missing(function (Request $request) {
                 $this->assertEquals(request(), $request);
                 return redirect()->to('awesome-js/alpine');
             });

        $this->get('/awesome-js/jquery')->assertRedirect('/awesome-js/alpine');
    }

    /** @test */
    public function can_pass_parameters_to_a_layout_file()
    {
        Livewire::component(ComponentForRouteRegistration::class);

        Route::get('/foo', ComponentForRouteRegistration::class);

        $this->withoutExceptionHandling()->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_handle_requests_after_application_is_created()
    {
        Livewire::component(ComponentForRouteRegistration::class);

        Route::get('/foo', ComponentForRouteRegistration::class);

        // After application is created,
        // request()->route() is null
        $this->createApplication();

        $this->withoutExceptionHandling()->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function component_uses_alias_instead_of_full_name_if_registered()
    {
        Livewire::component('component-alias', ComponentForRouteRegistration::class);

        Route::get('/foo', ComponentForRouteRegistration::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('component-alias');
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


class ComponentWithExtendsLayout extends Component
{
    public function render()
    {
        return view('null-view')->extends('layouts.app-extends', [
            'bar' => 'baz'
        ]);
    }
}

class ComponentWithCustomSection extends Component
{
    public $name = 'baz';

    public function render()
    {
        return view('show-name')->extends('layouts.app-custom-section')->section('body');
    }
}

class ComponentWithCustomLayout extends Component
{
    public function render()
    {
        return view('null-view')->layout('layouts.app-with-bar', [
            'bar' => 'baz'
        ]);
    }
}

class ComponentWithCustomSlotForLayout extends Component
{
    public $name = 'baz';

    public function render()
    {
        return view('show-name')->layout('layouts.app-custom-slot')->slot('main');
    }
}

class ComponentWithClassBasedComponentLayout extends Component
{
    public function render()
    {
        return view('null-view')->layout(\LegacyTests\AppLayout::class, [
            'bar' => 'baz'
        ]);
    }
}

class ComponentWithClassBasedComponentLayoutAndParams extends Component
{
    public function render()
    {
        return view('null-view')->layout(\LegacyTests\AppLayoutWithConstructor::class, [
            'bar' => 'baz'
        ]);
    }
}

class ComponentWithClassBasedComponentLayoutAndAttributes extends Component
{
    public function render()
    {
        return view('null-view')->layout(\LegacyTests\AppLayout::class, [
            'attributes' => [
                'class' => 'foo',
            ],
        ]);
    }
}

class ComponentWithAnonymousComponentLayout extends Component
{
    public function render()
    {
        return view('null-view')->layout('layouts.app-anonymous-component', [
            'bar' => 'baz'
        ]);
    }
}

class ComponentWithAnonymousComponentLayoutAndAttributes extends Component
{
    public function render()
    {
        return view('null-view')->layout('layouts.app-anonymous-component', [
            'attributes' => [
                'class' => 'foo',
            ],
        ]);
    }
}

class ComponentWithCustomParams extends Component
{
    public function render()
    {
        return view('null-view')->layoutData([
            'customParam' => 'foo'
        ]);
    }
}

class ComponentWithCustomParamsAndLayout extends Component
{
    public function render()
    {
        return view('null-view')->layout('layouts.data-test')->layoutData([
            'title' => 'livewire',
        ]);
    }
}

class FrameworkModel extends Model
{
    public function resolveRouteBinding($value, $field = null)
    {
        throw new ModelNotFoundException;
    }
}

class ComponentForRouteRegistration extends Component
{
    public $name = 'bar';

    public function render()
    {
        return view('show-name')->layout('layouts.app-with-bar', [
            'bar' => 'baz',
        ]);
    }
}

class ComponentWithModel extends Component
{
    public FrameworkModel $framework;
}
