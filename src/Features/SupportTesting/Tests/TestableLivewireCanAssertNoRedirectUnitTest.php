<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanAssertNoRedirectUnitTest extends \Tests\TestCase
{
    /** @test */
    public function can_assert_no_redirect()
    {
        $component = Livewire::test(NoRedirectComponent::class);

        $component->call('performNoRedirect');

        $component->assertNoRedirect();
    }

    /** @test */
    public function can_assert_no_redirect_will_fail_if_redirected()
    {
        $component = Livewire::test(NoRedirectComponent::class);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $component->call('performRedirect');
        $component->assertNoRedirect();
    }

    /** @test */
    public function can_assert_no_redirect_on_plain_component()
    {
        $component = Livewire::test(PlainRenderingComponent::class);
        $component->assertNoRedirect();
    }
}

class PlainRenderingComponent extends Component
{
    public function render()
    {
        return view('null-view');
    }
}

class NoRedirectComponent extends Component
{
    public function performRedirect()
    {
        $this->redirect('/some');
    }

    public function performNoRedirect()
    {
        $this->dispatch('noRedirect');
    }

    public function render()
    {
        return view('null-view');
    }
}
