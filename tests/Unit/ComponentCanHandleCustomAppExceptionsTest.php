<?php

namespace Tests\Unit;

use Exception;
use Livewire\Component;
use Livewire\Livewire;
use Throwable;

class ComponentCanHandleCustomAppExceptionsTest extends TestCase
{
    /** @test */
    public function a_livewire_component_can_handle_custom_application_exceptions()
    {
        Livewire::test(ComponentWithExcceptionHandling::class)
            ->call('doSomething');

    }

    /** @test */
    public function a_livewire_component_can_throws_custom_application_exceptions()
    {
        $this->expectException(AppHandledException::class);
        Livewire::test(ComponentWithoutExcceptionHandling::class)
            ->call('doSomething');

    }
}

class ComponentWithoutExcceptionHandling extends Component
{
    public function render()
    {
        return view('null-view');
    }

    public function doSomething()
    {
        throw new AppHandledException('Some handled error');
    }
}

class ComponentWithExcceptionHandling extends ComponentWithoutExcceptionHandling
{
    public function handleAppHandledException(Throwable $e)
    {
        // eg: add a custom validation error to the bag
        //$this->getErrorBag()->add('custom-error-key', $e->getMessage());
    }
}

class AppHandledException extends Exception
{
}
