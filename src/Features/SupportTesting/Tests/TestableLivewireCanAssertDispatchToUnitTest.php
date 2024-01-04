<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanAssertDispatchToUnitTest extends \Tests\TestCase
{
    /** @test */
    function can_assert_view_is()
    {
        Livewire::component('child', ChildComponent::class);
        Livewire::component('another-child', AnotherChildComponent::class);

        Livewire::test(DispatchToComponent::class)
            ->call('callDispatch')
            ->assertDispatchedTo('child', 'receive');

        Livewire::test(DispatchToComponent::class)
            ->call('callDispatch')
            ->assertDispatchedTo(['child', 'another-child'], 'receive')
        ;
    }
}

class DispatchToComponent extends Component
{
    public function callDispatch()
    {
        $this->dispatch('receive')->to('child', 'another-child');
    }

    function render()
    {
        return '<div>
             <livewire:child />
             <livewire:another-child />
        </div>';
    }
}

class ChildComponent extends Component
{
    function render()
    {
        return '<div></div>';
    }
}

class AnotherChildComponent extends Component
{
    function render()
    {
        return '<div></div>';
    }
}
