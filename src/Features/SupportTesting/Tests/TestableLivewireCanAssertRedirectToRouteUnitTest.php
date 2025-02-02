<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Tests\TestComponent;

class TestableLivewireCanAssertRedirectToRouteUnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Route::get('foo', function () {
            return true;
        })->name('foo');
    }

    function test_can_assert_a_redirect_to_a_route()
    {
        $component = Livewire::test(RedirectRouteComponent::class);

        $component->call('performRedirect');

        $component->assertRedirectToRoute('foo');
    }

    function test_can_detect_failed_redirect()
    {
        $component = Livewire::test(RedirectRouteComponent::class);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        $component->assertRedirectToRoute('foo');
    }
}

class RedirectRouteComponent extends TestComponent
{
    function performRedirect()
    {
        $this->redirectRoute('foo');
    }
}
