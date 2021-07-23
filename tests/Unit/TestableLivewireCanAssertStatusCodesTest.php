<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestableLivewireCanAssertStatusCodesTest extends TestCase
{
    /** @test */
    public function can_assert_a_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(NotFoundComponent::class);

        $component->assertStatus(404);
    }

    /** @test */
    public function can_assert_a_404_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(NotFoundComponent::class);

        $component->assertNotFound();
    }

    /** @test */
    public function can_assert_a_401_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(UnauthorizedComponent::class);

        $component->assertUnauthorized();
    }

    /** @test */
    public function can_assert_a_403_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(ForbiddenComponent::class);

        $component->assertForbidden();
    }

    /** @test */
    public function can_assert_status_and_continue_making_livewire_assertions()
    {
        Livewire::test(NormalComponent::class)
            ->assertStatus(200)
            ->assertSee('Hello!')
            ->assertSeeHtml('</example>');
    }
}

class NotFoundComponent extends Component
{
    public function render()
    {
        throw new HttpException(404);
    }
}

class UnauthorizedComponent extends Component
{
    public function render()
    {
        throw new HttpException(401);
    }
}

class ForbiddenComponent extends Component
{
    public function render()
    {
        throw new HttpException(403);
    }
}

class NormalComponent extends Component
{
    public function render()
    {
        return '<example>Hello!</example>';
    }
}
