<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class PublicMethodsAreAvailableInTheViewTest extends TestCase
{
    /** @test */
    public function public_methods_is_accessible_in_view_via_this()
    {
        Livewire::test(PublicMethodsInViewWithoutThisStub::class)
            ->assertSee('Your Name is Chris');
    }

    /** @test */
    public function public_methods_are_accessible_in_view_without_this()
    {
        Livewire::test(PublicMethodsInViewWithoutThisStub::class)
            ->assertSee('Your Name is Chris');
    }
}

class PublicMethodsInViewWithThisStub extends Component
{
    public function render()
    {
        return app('view')->make('call-name-with-this');
    }

    public function name($name)
    {
        return 'Your Name is '.$name;
    }
}

class PublicMethodsInViewWithoutThisStub extends Component
{
    public function render()
    {
        return app('view')->make('call-name');
    }

    public function name($name)
    {
        return 'Your Name is '.$name;
    }
}
