<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class TestableLivewireCanAssertRedirectUnitTest extends \Tests\TestCase
{
    #[Test]
    function can_assert_a_redirect_without_a_uri()
    {
        $component = Livewire::test(RedirectComponent::class);

        $component->call('performRedirect');

        $component->assertRedirect();
    }

    #[Test]
    function can_assert_a_redirect_with_a_uri()
    {
        $component = Livewire::test(RedirectComponent::class);

        $component->call('performRedirect');

        $component->assertRedirect('/some');
    }

    #[Test]
    function can_detect_failed_redirect()
    {
        $component = Livewire::test(RedirectComponent::class);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $component->assertRedirect();
    }
}

class RedirectComponent extends Component
{
    function performRedirect()
    {
        $this->redirect('/some');
    }

    function render()
    {
        return view('null-view');
    }
}
