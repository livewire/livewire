<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestableLivewireCanAssertStatusCodes extends TestCase
{
    /** @test */
    public function it_can_assert_a_404_status_code_when_an_exception_is_encountered()
    {
        $component = app(LivewireManager::class)->test(NotFoundComponent::class);

        $component->assertNotFound();
    }

    /** @test */
    public function it_can_assert_a_401_status_code_when_an_exception_is_encountered()
    {
        $component = app(LivewireManager::class)->test(UnauthorizedComponent::class);

        $component->assertUnauthorized();
    }

    /** @test */
    public function it_can_assert_a_403_status_code_when_an_exception_is_encountered()
    {
        $component = app(LivewireManager::class)->test(ForbiddenComponent::class);

        $component->assertForbidden();
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
