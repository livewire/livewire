<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class TestableLivewireCanAssertNoRedirectUnitTest extends \Tests\TestCase
{
    #[Test]
    function can_assert_no_redirect()
    {
        $component = Livewire::test(NoRedirectComponent::class);

        $component->call('performNoRedirect');

        $component->assertNoRedirect();
    }

    #[Test]
    function can_assert_no_redirect_will_fail_if_redirected()
    {
        $component = Livewire::test(NoRedirectComponent::class);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $component->call('performRedirect');
        $component->assertNoRedirect();
    }

    #[Test]
    function can_assert_no_redirect_on_plain_component()
    {
        $component = Livewire::test(PlainRenderingComponent::class);
        $component->assertNoRedirect();
    }
}

class PlainRenderingComponent extends Component
{
    function render()
    {
        return view('null-view');
    }
}

class NoRedirectComponent extends Component
{
    function performRedirect()
    {
        $this->redirect('/some');
    }

    function performNoRedirect()
    {
        $this->dispatch('noRedirect');
    }

    function render()
    {
        return view('null-view');
    }
}
