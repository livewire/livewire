<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanAssertViewIsUnitTest extends \Tests\TestCase
{
    /** @test */
    function can_assert_view_is()
    {
        Livewire::test(ViewComponent::class)
            ->assertViewIs('null-view');
    }
}

class ViewComponent extends Component
{
    function render()
    {
        return view('null-view');
    }
}
