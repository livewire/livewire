<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Livewire;
use Tests\TestComponent;

class TestableLivewireCanAssertViewIsUnitTest extends \Tests\TestCase
{
    function test_can_assert_view_is()
    {
        Livewire::test(ViewComponent::class)
            ->assertViewIs('null-view');
    }
}

class ViewComponent extends TestComponent
{
    function render()
    {
        return view('null-view');
    }
}
