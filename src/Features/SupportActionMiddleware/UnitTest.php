<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Authorize;
use Livewire\Attributes\Middleware;
use Livewire\Attributes\On;
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

    public function test_can_apply_middleware_with_class_name_for_unauthenticated_user()
    {
        $this->registerNamedRoute();

        $this->expectException(AuthenticationException::class);

        Livewire::test(new class extends TestComponent {
                #[Middleware(Authenticate::class)]
                public function protectedAction()
                {
                    Session::put('action-was-called', true);
                }
            })
            ->call('protectedAction')
            ->assertRedirectToRoute('login');

        $this->assertFalse(Session::has('action-was-called'));
    }

    public function test_can_apply_middleware_with_alias()
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

    public function test_can_apply_middleware_with_alias_for_unauthenticated_user()
    {
        $this->registerNamedRoute();

        $this->expectException(AuthenticationException::class);

        Livewire::test(new class extends TestComponent {
                #[Middleware('auth')]
                public function protectedAction()
                {
                    Session::put('action-was-called', true);
                }
            })
            ->call('protectedAction')
            ->assertRedirectToRoute('login');

        $this->assertFalse(Session::has('action-was-called'));
    }

    public function test_can_apply_multiple_middleware_to_action()
    {
        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware('auth')]
                #[Middleware(MiddlewareForbiddenTest::class)]
                public function protectedAction()
                {
                    Session::put('action-was-called', true);
                }
            })
            ->call('protectedAction')
            ->assertForbidden();

        $this->assertFalse(Session::has('action-was-called'));
    }

    public function test_can_apply_multiple_middleware_to_action_for_unauthenticated_user()
    {
        $this->registerNamedRoute();

        $this->expectException(AuthenticationException::class);

        Livewire::test(new class extends TestComponent {
            #[Middleware('auth')]
            #[Middleware(MiddlewareForbiddenTest::class)]
            public function protectedAction()
            {
                Session::put('action-was-called', true);
            }
        })
        ->call('protectedAction')
        ->assertRedirectToRoute('login');

        $this->assertFalse(Session::has('action-was-called'));
    }

    public function test_middleware_works_with_event_listeners()
    {
        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[On('some-event')]
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
        $this->registerNamedRoute();

        $this->expectException(AuthenticationException::class);

        Livewire::test(new class extends TestComponent {
            #[On('protected-event')]
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

    public function test_middleware_integrates_with_multiple_actions_for_unauthenticated_user()
    {
        $this->registerNamedRoute();

        $this->expectException(AuthenticationException::class);

        Livewire::test(new class extends TestComponent {
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
            ->call('publicAction')
            ->assertOk()
            ->call('protectedAction')
            ->assertRedirectToRoute('login');

        $this->assertTrue(Session::has('public-action-called'));
        $this->assertFalse(Session::has('protected-action-called'));
    }

    public function test_can_redirect_inside_action_when_middleware_passed()
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

        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware('auth')]
                public function goSomewhereElse()
                {
                    return $this->redirect('/somewhere-else');
                }
            })
            ->call('goSomewhereElse')
            ->assertRedirect('/somewhere-else');
    }

    public function test_can_redirect_inside_action_when_middleware_passed_with_event()
    {
        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware('auth')]
                #[On('go-somewhere')]
                public function goSomewhere()
                {
                    return redirect('/somewhere');
                }
            })
            ->dispatch('go-somewhere')
            ->assertRedirect('/somewhere');

        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware('auth')]
                #[On('go-somewhere-else')]
                public function goSomewhereElse()
                {
                    return $this->redirect('/somewhere-else');
                }
            })
            ->dispatch('go-somewhere-else')
            ->assertRedirect('/somewhere-else');
    }
    
    public function test_can_abort_inside_middleware()
    {
        Livewire::test(new class extends TestComponent {
            #[Middleware(MiddlewareAbortTest::class)]
            public function goSomewhere()
            {
                Session::put('should-never-be-set', true);
            }
        })
        ->call('goSomewhere')
        ->assertNotFound();

        $this->assertFalse(Session::has('should-never-be-set'));
    }

    public function test_can_redirect_inside_middleware()
    {
        $this->registerNamedRoute();

        Livewire::test(new class extends TestComponent {
            #[Middleware(MiddlewareRedirectTest::class)]
            public function goSomewhere()
            {
                Session::put('should-never-be-set', true);
            }
        })
        ->call('goSomewhere')
        ->assertStatus(302);

        $this->assertFalse(Session::has('should-never-be-set'));
    }

    public function test_can_throw_exception_inside_middleware()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Middleware throws an exception');

        Livewire::test(new class extends TestComponent {
            #[Middleware(MiddlewareExceptionTest::class)]
            public function goSomewhere()
            {
                Session::put('should-never-be-set', true);
            }
        })
        ->call('goSomewhere');

        $this->assertFalse(Session::has('should-never-be-set'));
    }

    public function test_middleware_attribute_take_precedence_over_authorize()
    {
        $this->registerNamedRoute();

        $this->expectException(AuthenticationException::class);

        Gate::define('not-triggered', fn () => false);

        Livewire::test(new class extends TestComponent {
            #[Middleware('auth')]
            #[Authorize('not-triggered')]
            public function goSomewhere()
            {
                Session::put('should-never-be-set', true);
            }
        })
        ->call('goSomewhere')
        ->assertRedirectToRoute('login');

        $this->assertFalse(Session::has('should-never-be-set'));
    }

    public function test_middleware_attribute_take_precedence_over_authorize_with_event_listener()
    {
        $this->registerNamedRoute();

        $this->expectException(AuthenticationException::class);

        Gate::define('not-triggered', fn () => false);

        Livewire::test(new class extends TestComponent {
            #[Middleware('auth')]
            #[Authorize('not-triggered')]
            #[On('go-somewhere')]
            public function goSomewhere()
            {
                Session::put('should-never-be-set', true);
            }
        })
        ->dispatch('go-somewhere')
        ->assertRedirectToRoute('login');

        $this->assertFalse(Session::has('should-never-be-set'));
    }

    public function test_authorize_middleware_excluded_from_middleware_attribute()
    {
        Gate::define('interact', fn () => false);

        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware('auth')]
                #[Middleware('can:interact')]
                public function goSomewhere()
                {
                    Session::put('should-never-be-set', true);
                }
            })
            ->call('goSomewhere')
            ->assertOk();

        $this->assertTrue(Session::has('should-never-be-set'));
    }

    public function test_authorize_middleware_excluded_from_middleware_attribute_with_event_listener()
    {
        Gate::define('interact', fn () => false);

        Livewire::actingAs(MiddlewareTestUser::find(1))
            ->test(new class extends TestComponent {
                #[Middleware('auth')]
                #[Middleware('can:interact')]
                #[On('go-somewhere')]
                public function goSomewhere()
                {
                    Session::put('should-never-be-set', true);
                }
            })
            ->dispatch('go-somewhere')
            ->assertOk();

        $this->assertTrue(Session::has('should-never-be-set'));
    }

    public function test_unresolved_middleware_class_throws_exception()
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Target class [ghabriel25] does not exist.');

        Livewire::test(new class extends TestComponent {
            #[Middleware('ghabriel25')]
            public function goSomewhere()
            {
                Session::put('should-never-be-set', true);
            }
        })
        ->call('goSomewhere')
        ->assertStatus(500);

        $this->assertFalse(Session::has('should-never-be-set'));
    }

    protected function registerNamedRoute()
    {
        Route::livewire('/login', new class extends TestComponent {})->name('login');
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

class MiddlewareForbiddenTest
{
    public function handle(Request $request, \Closure $next)
    {
        abort(403);
    }
}

class MiddlewareAbortTest
{
    public function handle(Request $request, \Closure $next)
    {
        abort(404);
    }
}

class MiddlewareRedirectTest
{
    public function handle(Request $request, \Closure $next)
    {
        return redirect('/login');
    }
}

class MiddlewareExceptionTest
{
    public function handle(Request $request, \Closure $next)
    {
        throw new \Exception('Middleware throws an exception');
    }
}