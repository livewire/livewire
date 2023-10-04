<?php

namespace Livewire\Features\SupportTesting\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestComponent;

class TestableLivewireCanAssertStatusCodesUnitTest extends \Tests\TestCase
{
    /** @test */
    function can_assert_a_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(NotFoundComponent::class);

        $component->assertStatus(404);
    }

    /** @test */
    function can_assert_a_404_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(NotFoundComponent::class);

        $component->assertNotFound();
    }

    /** @test */
    function can_assert_a_401_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(UnauthorizedComponent::class);

        $component->assertUnauthorized();
    }

    /** @test */
    function can_assert_a_403_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(ForbiddenComponent::class);

        $component->assertForbidden();
    }

    /** @test */
    function can_assert_a_403_status_code_when_an_exception_is_encountered_on_an_action()
    {
        $component = Livewire::test(new class extends TestComponent {
            public function someAction() {
                throw new \Illuminate\Auth\Access\AuthorizationException;
            }
        });

        $component
            ->call('someAction')
            ->assertForbidden();
    }

    /** @test */
    function can_assert_status_and_continue_making_livewire_assertions()
    {
        Livewire::test(NormalComponent::class)
            ->assertStatus(200)
            ->assertSee('Hello!')
            ->assertSeeHtml('</example>');
    }
}

class NotFoundComponent extends Component
{
    function render()
    {
        throw new HttpException(404);
    }
}

class UnauthorizedComponent extends Component
{
    function render()
    {
        throw new HttpException(401);
    }
}

class ForbiddenComponent extends Component
{
    function render()
    {
        throw new HttpException(403);
    }
}

class NormalComponent extends Component
{
    function render()
    {
        return '<example>Hello!</example>';
    }
}
