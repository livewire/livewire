<?php

namespace Livewire\Mechanisms\ExtendBlade;

use ErrorException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\Exceptions\BypassViewHandler;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_livewire_only_directives_apply_to_livewire_components_and_not_normal_blade()
    {
        Livewire::directive('foo', function ($expression) {
            return 'bar';
        });

        $output = Blade::render('
            <div>@foo</div>

            @livewire(\Livewire\Mechanisms\ExtendBlade\ExtendBladeTestComponent::class)

            <div>@foo</div>
        ');

        $this->assertCount(3, explode('@foo', $output));
    }

    public function test_livewire_only_precompilers_apply_to_livewire_components_and_not_normal_blade()
    {
        Livewire::precompiler(function ($string) {
            return preg_replace_callback('/@foo/sm',  function ($matches) {
                return 'bar';
            }, $string);
        });

        $output = Blade::render('
            <div>@foo</div>

            @livewire(\Livewire\Mechanisms\ExtendBlade\ExtendBladeTestComponent::class)

            <div>@foo</div>
        ');

        $this->assertCount(3, explode('@foo', $output));
    }

    public function test_this_keyword_will_reference_the_livewire_component_class()
    {
        Livewire::test(ComponentForTestingThisKeyword::class)
            ->assertSee(ComponentForTestingThisKeyword::class);
    }

    public function test_this_directive_returns_javascript_component_object_string()
    {
        Livewire::test(ComponentForTestingDirectives::class)
            ->assertDontSee('@this')
            ->assertSee('window.Livewire.find(');
    }

    public function test_this_directive_can_be_used_in_nested_blade_component()
    {
        Livewire::test(ComponentForTestingNestedThisDirective::class)
            ->assertDontSee('@this')
            ->assertSee('window.Livewire.find(');
    }

    public function test_public_property_is_accessible_in_view_via_this()
    {
        Livewire::test(PublicPropertiesInViewWithThisStub::class)
            ->assertSee('Caleb');
    }

    public function test_public_properties_are_accessible_in_view_without_this()
    {
        Livewire::test(PublicPropertiesInViewWithoutThisStub::class)
            ->assertSee('Caleb');
    }

    public function test_protected_property_is_accessible_in_view_via_this()
    {
        Livewire::test(ProtectedPropertiesInViewWithThisStub::class)
            ->assertSee('Caleb');
    }

    public function test_protected_properties_are_not_accessible_in_view_without_this()
    {
        Livewire::test(ProtectedPropertiesInViewWithoutThisStub::class)
            ->assertDontSee('Caleb');
    }

    public function test_normal_errors_thrown_from_inside_a_livewire_view_are_wrapped_by_the_blade_handler()
    {
        // Blade wraps thrown exceptions in "ErrorException" by default.
        $this->expectException(ErrorException::class);

        Livewire::component('foo', NormalExceptionIsThrownInViewStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    public function test_livewire_errors_thrown_from_inside_a_livewire_view_bypass_the_blade_wrapping()
    {
        // Exceptions that use the "BypassViewHandler" trait remain unwrapped.
        $this->expectException(SomeCustomLivewireException::class);

        Livewire::component('foo', LivewireExceptionIsThrownInViewStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    public function test_errors_thrown_by_abort_404_function_are_not_wrapped()
    {
        $this->expectException(NotFoundHttpException::class);

        Livewire::component('foo', Abort404IsThrownInComponentMountStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    public function test_errors_thrown_by_abort_500_function_are_not_wrapped()
    {
        $this->expectException(HttpException::class);

        Livewire::component('foo', Abort500IsThrownInComponentMountStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    public function test_errors_thrown_by_authorization_exception_function_are_not_wrapped()
    {
        $this->expectException(AuthorizationException::class);

        Livewire::component('foo', AuthorizationExceptionIsThrownInComponentMountStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }
}

class ExtendBladeTestComponent extends Component
{
    public function render()
    {
        return '<div>@foo</div>';
    }
}

class ComponentForTestingThisKeyword extends Component
{
    public function render()
    {
        return '<div>{{ get_class($this) }}</div>';
    }
}

class ComponentForTestingDirectives extends Component
{
    public function render()
    {
        return '<div>@this</div>';
    }
}

class ComponentForTestingNestedThisDirective extends Component
{
    public function render()
    {
        return "<div>@component('components.this-directive')@endcomponent</div>";
    }
}

class PublicPropertiesInViewWithThisStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class PublicPropertiesInViewWithoutThisStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}


class ProtectedPropertiesInViewWithThisStub extends Component
{
    protected $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ProtectedPropertiesInViewWithoutThisStub extends Component
{
    protected $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}

class SomeCustomLivewireException extends \Exception
{
    use BypassViewHandler;
}

class NormalExceptionIsThrownInViewStub extends Component
{
    public function render()
    {
        return app('view')->make('execute-callback', [
            'callback' => function () {
                throw new Exception();
            },
        ]);
    }
}

class LivewireExceptionIsThrownInViewStub extends Component
{
    public function render()
    {
        return app('view')->make('execute-callback', [
            'callback' => function () {
                throw new SomeCustomLivewireException();
            },
        ]);
    }
}

class Abort404IsThrownInComponentMountStub extends TestComponent
{
    public function mount()
    {
        abort(404);
    }
}

class Abort500IsThrownInComponentMountStub extends TestComponent
{
    public function mount()
    {
        abort(500);
    }
}

class AuthorizationExceptionIsThrownInComponentMountStub extends TestComponent
{
    public function mount()
    {
        throw new AuthorizationException();
    }
}
