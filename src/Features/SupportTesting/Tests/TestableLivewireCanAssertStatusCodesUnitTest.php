<?php

namespace Livewire\Features\SupportTesting\Tests;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestComponent;

class TestableLivewireCanAssertStatusCodesUnitTest extends \Tests\TestCase
{
    function test_can_assert_a_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(NotFoundComponent::class);

        $component->assertStatus(404);
    }

    function test_can_assert_a_404_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(NotFoundComponent::class);

        $component->assertNotFound();
    }

    function test_can_assert_a_401_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(UnauthorizedComponent::class);

        $component->assertUnauthorized();
    }

    function test_can_assert_a_403_status_code_when_an_exception_is_encountered()
    {
        $component = Livewire::test(ForbiddenComponent::class);

        $component->assertForbidden();
    }

    function test_can_assert_a_403_status_code_when_an_exception_is_encountered_on_an_action()
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

    function test_can_assert_status_and_continue_making_livewire_assertions()
    {
        Livewire::test(NormalComponent::class)
            ->assertStatus(200)
            ->assertSee('Hello!')
            ->assertSeeHtml('</example>');
    }

    function test_can_assert_not_found_when_an_action_cannot_find_a_model()
    {
        Livewire::test(MissingModelComponent::class)
            ->call('showFullDetails', 0)
            ->assertNotFound();
    }

    function test_missing_model_exceptions_are_rethrown_when_exception_handling_is_disabled()
    {
        $this->withoutExceptionHandling();

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(MissingModelComponent::class)
            ->call('showFullDetails', 0);
    }
}

class MissingModelComponent extends TestComponent
{
    public function showFullDetails($id)
    {
        throw (new ModelNotFoundException)->setModel('Post', [$id]);
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
