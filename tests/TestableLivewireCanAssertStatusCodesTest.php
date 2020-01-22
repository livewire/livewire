<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Livewire\Component;
use Livewire\LivewireManager;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestableLivewireCanAssertStatusCodesTest extends TestCase
{
    /** @test */
    public function can_assert_a_status_code_when_an_exception_is_encountered()
    {
        $component = app(LivewireManager::class)->test(NotFoundComponent::class);

        $component->assertStatus(404);
    }

    /** @test */
    public function can_assert_a_404_status_code_when_an_exception_is_encountered()
    {
        if (version_compare(Application::VERSION, '5.6.12', '<')) {
            $this->markTestSkipped('assertNotFound is unavailable prior to Laravel 5.6.12');
        }

        $component = app(LivewireManager::class)->test(NotFoundComponent::class);

        $component->assertNotFound();
    }

    /** @test */
    public function can_assert_a_401_status_code_when_an_exception_is_encountered()
    {
        if (version_compare(Application::VERSION, '5.8.24', '<')) {
            $this->markTestSkipped('assertUnauthorized is unavailable prior to Laravel 5.8.24');
        }

        $component = app(LivewireManager::class)->test(UnauthorizedComponent::class);

        $component->assertUnauthorized();
    }

    /** @test */
    public function can_assert_a_403_status_code_when_an_exception_is_encountered()
    {
        if (version_compare(Application::VERSION, '5.6.12', '<')) {
            $this->markTestSkipped('assertForbidden is unavailable prior to Laravel 5.6.12');
        }

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
