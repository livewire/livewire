<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireManager;

class TestableLivewireCanAssertNotRedirectedTest extends TestCase
{
    /** @test */
    public function can_assert_not_redirected()
    {
        $component = Livewire::test(NotRedirectComponent::class);

        $component->call('performNoRedirect');

        $component->assertNotRedirected();
    }

    /** @test */
    public function can_assert_not_redirected_will_if_redirected()
    {
        $component = Livewire::test(NotRedirectComponent::class);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $component->call('performRedirect');
        $component->assertNotRedirected();
    }
}

class NotRedirectComponent extends Component
{
    public function performRedirect()
    {
        $this->redirect('/some');
    }

    public function performNoRedirect()
    {
        $this->emit('noRedirect');
    }

    public function render()
    {
        return view('null-view');
    }
}
