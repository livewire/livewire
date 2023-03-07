<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Laravel\Dusk\Browser;
use Livewire\Component as BaseComponent;
use Livewire\Livewire;
use Sushi\Sushi;
use Symfony\Component\HttpFoundation\Response;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    protected function getEnvironmentSetUp($app) {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.providers.users.model', User::class);
    }

    public static function getApplicationModificationClosure() {
        return function() {
            Livewire::addPersistentMiddleware(AllowListedMiddleware::class);

            Route::get('/force-login/{userId}', function ($userId) {
                Auth::login(User::find($userId));

                return 'You\'re logged in.';
            })->middleware('web');

            Route::get('/force-logout', function () {
                Auth::logout();

                return 'You\'re logged out.';
            })->middleware('web');

            Route::get('/with-authentication/livewire-dusk/{component}', function ($component) {
                $class = urldecode($component);

                return app()->call(app('livewire')->new($class));
            })->middleware(['web', 'auth']);

            Gate::policy(Post::class, PostPolicy::class);

            Route::get('/with-authorization/{post}/livewire-dusk/{component}', function (Post $post, $component) {
                $class = urldecode($component);

                return app()->call(new $class);
            })->middleware(['web', 'auth', 'can:update,post']);
        };
    }
    public function test_that_persistent_middleware_is_applied_to_subsequent_livewire_requests()
    {
        // @todo: Copy implementation from V2 for persistent middleware and ensure localisation and subdirectory hosting are supported. https://github.com/livewire/livewire/pull/5490
        Livewire::visit([Component::class, 'child' => NestedComponent::class])
            // See allow-listed middleware from original request.
            ->assertSeeIn('@middleware', json_encode([AllowListedMiddleware::class, BlockListedMiddleware::class]))

            ->waitForLivewire()->click('@refresh')

            // See that the original request middleware was re-applied.
            ->assertSeeIn('@middleware', json_encode([AllowListedMiddleware::class]))

            ->waitForLivewire()->click('@showNested')

            // Even to nested components shown AFTER the first load.
            ->assertSeeIn('@middleware', json_encode([AllowListedMiddleware::class]))
            ->assertSeeIn('@nested-middleware', json_encode([AllowListedMiddleware::class]))

            ->waitForLivewire()->click('@refreshNested')

            // Make sure they are still applied when stand-alone requests are made to that component.
            ->assertSeeIn('@middleware', json_encode([AllowListedMiddleware::class]))
            ->assertSeeIn('@nested-middleware', json_encode([AllowListedMiddleware::class]))
            ;
    }

    public function test_that_authentication_middleware_is_re_applied()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/force-login/1')
                ->visit('/with-authentication/livewire-dusk/'.urlencode(Component::class))
                ->waitForLivewireToLoad()
                // We're going to make a fetch request, but store the request payload
                // so we can replay it from a different page.
                ->tap(function ($b) {
                    $script = <<<'JS'
                        let unDecoratedFetch = window.fetch
                        let decoratedFetch = (...args) => {
                            window.localStorage.setItem(
                                'lastFetchArgs',
                                JSON.stringify(args),
                            )

                            return unDecoratedFetch(...args)
                        }
                        window.fetch = decoratedFetch
JS;

                    $b->script($script);
                })
                ->waitForLivewire()->click('@refresh')
                // Now we logout.
                ->visit('/force-logout')
                // Now we try and re-run the request payload, expecting that
                // the "auth" middleware will be applied, recognize we've
                // logged out and throw an error in the response.
                ->tap(function ($b) {
                    $script = <<<'JS'
                        let args = JSON.parse(localStorage.getItem('lastFetchArgs'))

                        window.fetch(...args).then(i => i.text()).then(response => {
                            document.body.textContent = 'response-ready: '+JSON.stringify(response)
                        })
JS;

                    $b->script($script);
                })
                ->waitForText('response-ready: ')
                ->tinker()
                ->assertDontSee('Protected Content');
            ;
        });
    }

    public function test_that_authorization_middleware_is_re_applied()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/force-login/1')
                ->visit('/with-authorization/1/livewire-dusk/'.urlencode(Component::class))
                ->waitForLivewireToLoad()
                ->tap(function ($b) {
                    $script = <<<'JS'
                        let unDecoratedFetch = window.fetch
                        let decoratedFetch = (...args) => {
                            window.localStorage.setItem(
                                'lastFetchArgs',
                                JSON.stringify(args),
                            )

                            return unDecoratedFetch(...args)
                        }
                        window.fetch = decoratedFetch
JS;

                    $b->script($script);
                })
                ->waitForLivewire()->click('@refresh')
                ->visit('/force-login/2')
                ->tap(function ($b) {
                    $script = <<<'JS'
                        let args = JSON.parse(localStorage.getItem('lastFetchArgs'))

                        window.fetch(...args).then(i => i.text()).then(response => {
                            document.body.textContent = 'response-ready: '+JSON.stringify(response)
                        })
JS;

                    $b->script($script);
                })
                ->waitForText('response-ready: ')
                ->assertDontSee('Protected Content');
            ;
        });
    }
}

class Component extends BaseComponent
{
    public static $loggedMiddleware = [];

    public $middleware = [];
    public $showNested = false;

    public function showNestedComponent()
    {
        $this->showNested = true;
    }

    public function render()
    {
        $this->middleware = static::$loggedMiddleware;

        return <<<'HTML'
<div>
    <span dusk="middleware">@json($middleware)</span>

    <button wire:click="$refresh" dusk="refresh">Refresh</button>
    <button wire:click="showNestedComponent" dusk="showNested">Show Nested</button>

    <h1>Protected Content</h1>

    @if ($showNested)
        @livewire(\Livewire\Mechanisms\PersistentMiddleware\NestedComponent::class)
    @endif
</div>
HTML;
    }
}

class NestedComponent extends BaseComponent
{
    public $middleware = [];

    public function render()
    {
        $this->middleware = Component::$loggedMiddleware;

        return <<<'HTML'
<div>
    <span dusk="nested-middleware">@json($middleware)</span>

    <button wire:click="$refresh" dusk="refreshNested">Refresh</button>
</div>
HTML;
    }
}

class AllowListedMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        Component::$loggedMiddleware[] = static::class;

        return $next($request);
    }
}

class BlockListedMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        Component::$loggedMiddleware[] = static::class;

        return $next($request);
    }
}

class User extends AuthUser
{
    use Sushi;

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected $rows = [
        [
            'name' => 'First User',
            'email' => 'first@laravel-livewire.com',
            'password' => '',
        ],
        [
            'name' => 'Second user',
            'email' => 'second@laravel-livewire.com',
            'password' => '',
        ],
    ];
}

class Post extends Model
{
    use Sushi;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $rows = [
        ['title' => 'First', 'user_id' => 1],
        ['title' => 'Second', 'user_id' => 2],
    ];
}

class PostPolicy
{
    public function update(User $user, Post $post)
    {
        return (int) $post->user_id === (int) $user->id;
    }
}

