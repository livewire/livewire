<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Livewire;
use Tests\TestComponent;

class TestableLivewireCanAssertNoRedirectUnitTest extends \Tests\TestCase
{
    function test_can_assert_no_redirect()
    {
        $component = Livewire::test(NoRedirectComponent::class);

        $component->call('performNoRedirect');

        $component->assertNoRedirect();
    }

    function test_can_assert_no_redirect_will_fail_if_redirected()
    {
        $component = Livewire::test(NoRedirectComponent::class);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $component->call('performRedirect');
        $component->assertNoRedirect();
    }

    function test_can_assert_no_redirect_on_plain_component()
    {
        $component = Livewire::test(PlainRenderingComponent::class);
        $component->assertNoRedirect();
    }
}

class PlainRenderingComponent extends TestComponent
{
}

class NoRedirectComponent extends TestComponent
{
    function performRedirect()
    {
        $this->redirect('/some');
    }

    function performNoRedirect()
    {
        $this->dispatch('noRedirect');
    }
}
