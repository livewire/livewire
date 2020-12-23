<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireManager;

class TestableLivewireCanAssertRedirectTest extends TestCase
{
    /** @test */
    public function can_assert_a_redirect_without_a_uri()
    {
        $component = Livewire::test(RedirectComponent::class);

        $component->call('performRedirect');

        $component->assertRedirect();
    }

    /** @test */
    public function can_assert_a_redirect_with_a_uri()
    {
        $component = Livewire::test(RedirectComponent::class);

        $component->call('performRedirect');

        $component->assertRedirect('/some');
    }

    /** @test */
    public function can_detect_failed_redirect()
    {
        $component = Livewire::test(RedirectComponent::class);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $component->assertRedirect();
    }
}

class RedirectComponent extends Component
{
    public function performRedirect()
    {
        $this->redirect('/some');
    }

    public function render()
    {
        return view('null-view');
    }
}
