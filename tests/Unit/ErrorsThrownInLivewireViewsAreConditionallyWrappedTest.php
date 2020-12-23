<?php

namespace Tests\Unit;

use Exception;
use ErrorException;
use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\View;
use Livewire\Exceptions\BypassViewHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorsThrownInLivewireViewsAreConditionallyWrappedTest extends TestCase
{
    /** @test */
    public function normal_errors_thrown_from_inside_a_livewire_view_are_wrapped_by_the_blade_handler()
    {
        // Blade wraps thrown exceptions in "ErrorException" by default.
        $this->expectException(ErrorException::class);

        Livewire::component('foo', NormalExceptionIsThrownInViewStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    /** @test */
    public function livewire_errors_thrown_from_inside_a_livewire_view_bypass_the_blade_wrapping()
    {
        // Exceptions that use the "BypassViewHandler" trait remain unwrapped.
        $this->expectException(SomeCustomLivewireException::class);

        Livewire::component('foo', LivewireExceptionIsThrownInViewStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    /** @test */
    public function errors_thrown_by_abort_404_function_are_not_wrapped()
    {
        $this->expectException(NotFoundHttpException::class);

        Livewire::component('foo', Abort404IsThrownInComponentMountStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    /** @test */
    public function errors_thrown_by_abort_500_function_are_not_wrapped()
    {
        $this->expectException(HttpException::class);

        Livewire::component('foo', Abort500IsThrownInComponentMountStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
    }

    /** @test */
    public function errors_thrown_by_authorization_exception_function_are_not_wrapped()
    {
        $this->expectException(AuthorizationException::class);

        Livewire::component('foo', AuthorizationExceptionIsThrownInComponentMountStub::class);

        View::make('render-component', ['component' => 'foo'])->render();
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
                throw new Exception;
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
                throw new SomeCustomLivewireException;
            },
        ]);
    }
}

class Abort404IsThrownInComponentMountStub extends Component
{
    public function mount()
    {
        abort(404);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class Abort500IsThrownInComponentMountStub extends Component
{
    public function mount()
    {
        abort(500);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class AuthorizationExceptionIsThrownInComponentMountStub extends Component
{
    public function mount()
    {
        throw new AuthorizationException;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
