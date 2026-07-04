<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Middleware;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\TestCase;
use Tests\TestComponent;

class UnitTest extends TestCase
{
    public function test_can_apply_middleware_with_class_name()
    {        
        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware(Authenticate::class)]
                public function protectedAction()
                {
                    Session::put('action-was-called', true);
                }
            })
            ->call('protectedAction')
            ->assertOk();

        $this->assertTrue(Session::has('action-was-called'));
    }

    public function test_can_apply_single_middleware_to_action()
    {
        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware('auth')]
                public function protectedAction()
                {
                    Session::put('action-was-called', true);
                }
            })
            ->call('protectedAction')
            ->assertOk();

        $this->assertTrue(Session::has('action-was-called'));
    }

    public function test_can_apply_multiple_middleware_to_action()
    {
        Route::livewire('/login', new class extends TestComponent {})->name('login');

        $this->expectException(AuthenticationException::class);

        Livewire::test(new class extends TestComponent {
            #[Middleware('auth')]
            #[Middleware(MiddlewareTest::class)]
            public function protectedAction()
            {
                Session::put('action-was-called', true);
            }
        })
        ->call('protectedAction')
        ->assertRedirectToRoute('login');

        $this->assertFalse(Session::has('action-was-called'));
        $this->assertTrue(Session::has('not-authenticated'));
    }

    public function test_middleware_works_with_event_listeners()
    {
        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[\Livewire\Attributes\On('some-event')]
                #[Middleware('auth')]
                public function handleEvent()
                {
                    Session::put('event-was-handled', true);
                }
            })
            ->dispatch('some-event')
            ->assertOk();

        $this->assertTrue(Session::has('event-was-handled'));
    }

    public function test_middleware_on_event_listener_prevents_unauthorized_calls()
    {
        Route::livewire('/login', new class extends TestComponent {})->name('login');

        $this->expectException(AuthenticationException::class);

        Livewire::test(new class extends TestComponent {
            #[\Livewire\Attributes\On('protected-event')]
            #[Middleware('auth')]
            public function handleEvent()
            {
                Session::put('should-never-be-set', true);
            }
        })
        ->dispatch('protected-event')
        ->assertRedirectToRoute('login');

        $this->assertFalse(Session::has('should-never-be-set'));
    }

    public function test_middleware_integrates_with_multiple_actions()
    {
        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware('auth')]
                public function protectedAction()
                {
                    Session::put('protected-action-called', true);
                }

                public function publicAction()
                {
                    Session::put('public-action-called', true);
                }
            })
            ->call('protectedAction')
            ->assertOk()
            ->call('publicAction')
            ->assertOk();

        $this->assertTrue(Session::has('protected-action-called'));
        $this->assertTrue(Session::has('public-action-called'));
    }

    public function test_any_redirect_inside_action_still_working()
    {
        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware('auth')]
                public function goSomewhere()
                {
                    return redirect('/somewhere');
                }
            })
            ->call('goSomewhere')
            ->assertRedirect('/somewhere');
    }
}

class MiddlewareTestUser extends AuthUser
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'First User', 'email' => 'first@example.com', 'password' => ''],
        ['id' => 2, 'name' => 'Second User', 'email' => 'second@example.com', 'password' => ''],
    ];
}

class MiddlewareTestPost extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'title' => 'First', 'user_id' => 1],
    ];
}

class MiddlewareTest
{
    public function handle(Request $request, \Closure $next)
    {
        if(! $request->user()) {
            Session::put('not-authenticated', true);
        }
        
        return $next($request);
    }
}