<?php



namespace Livewire\Mechanisms\PersistentMiddleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Component as BaseComponent;
use Livewire\Livewire;
use Sushi\Sushi;
use Symfony\Component\HttpFoundation\Response;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    protected function getEnvironmentSetUp($app) {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton(\Illuminate\Contracts\Http\Kernel::class, HttpKernel::class);
    }

    public static function tweakApplicationHook() {
        return function() {
            Livewire::addPersistentMiddleware([AllowListedMiddleware::class, IsBanned::class]);

            // Overwrite the default route for these tests, so the middleware is included
            Route::get('livewire-dusk/{component}', function ($component) {
                $class = urldecode($component);

                return app()->call(app('livewire')->new($class));
            })->middleware(['web', AllowListedMiddleware::class, BlockListedMiddleware::class]);

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

            Route::get('/with-redirects/livewire-dusk/{component}', function ($component) {
                $class = urldecode($component);

                return app()->call(app('livewire')->new($class));
            })->middleware(['web', 'auth', IsBanned::class]);

            Gate::policy(Post::class, PostPolicy::class);

            Route::get('/with-authorization/{post}/livewire-dusk/{component}', function (Post $post, $component) {
                $class = urldecode($component);

                return app()->call(new $class);
            })->middleware(['web', 'auth', 'can:update,post']);

            Route::get('/with-authorization/{post}/inline-auth', Component::class)
                ->middleware(['web', 'auth', 'can:update,post']);
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
        Livewire::visit(Component::class)
            ->visit('/with-authentication/livewire-dusk/'.urlencode(Component::class))
            ->assertDontSee('Protected Content')
            ->visit('/force-login/1')
            ->visit('/with-authentication/livewire-dusk/'.urlencode(Component::class))
            ->waitForLivewireToLoad()
            ->assertSee('Protected Content')
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
            ->waitForLivewire()->click('@changeProtected')
            ->assertDontSee('Protected Content')
            ->assertSee('Still Secure Content')
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
            ->assertDontSee('Protected Content');
        ;
    }

    public function test_that_authorization_middleware_is_re_applied()
    {
        Livewire::visit(Component::class)
            ->visit('/with-authorization/1/livewire-dusk/'.urlencode(Component::class))
            ->assertDontSee('Protected Content')
            ->visit('/force-login/1')
            ->visit('/with-authorization/1/livewire-dusk/'.urlencode(Component::class))
            ->assertSee('Protected Content')
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
            ->waitForLivewire()->click('@changeProtected')
            ->assertDontSee('Protected Content')
            ->assertSee('Still Secure Content')
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
            ->assertDontSee('Protected Content')
        ;
    }

    public function test_that_persistent_middleware_redirects_on_subsequent_requests()
    {
        Livewire::visit(Component::class)
            ->tap(function () {
                User::where('id', 1)->update(['banned' => false]); // Reset the user. Sometimes it's cached(?.
            })
            ->visit('/force-login/1')
            ->visit('/with-redirects/livewire-dusk/' . urlencode(Component::class))
            ->assertSee('Protected Content')
            ->tap(function () {
                User::where('id', 1)->update(['banned' => true]);
            })
            ->waitForLivewire()
            ->click('@refresh')
            ->assertPathIs('/force-logout')
        ;
    }

    public function test_that_authorization_middleware_is_re_applied_on_page_components()
    {
        // This test relies on "app('router')->subsituteImplicitBindingsUsing()"...
        if (app()->version() < '10.37.1') {
            $this->markTestSkipped();
        }

        Livewire::visit(Component::class)
            ->visit('/with-authorization/1/inline-auth')
            ->assertDontSee('Protected Content')
            ->visit('/force-login/1')
            ->visit('/with-authorization/1/inline-auth')
            ->assertSee('Protected Content')
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
            ->waitForLivewire()->click('@changeProtected')
            ->assertDontSee('Protected Content')
            ->assertSee('Still Secure Content')
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
            ->assertDontSee('Protected Content')
        ;
    }
}

class HttpKernel extends Kernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \Orchestra\Testbench\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}

class Component extends BaseComponent
{
    public static $loggedMiddleware = [];

    public $middleware = [];
    public $showNested = false;
    public $changeProtected = false;

    public function mount(Post $post)
    {
        //
    }

    public function showNestedComponent()
    {
        $this->showNested = true;
    }

    public function toggleProtected()
    {
        $this->changeProtected = ! $this->changeProtected;
    }

    public function render()
    {
        $this->middleware = static::$loggedMiddleware;

        return <<<'HTML'
<div>
    <span dusk="middleware">@json($middleware)</span>

    <button wire:click="$refresh" dusk="refresh">Refresh</button>
    <button wire:click="toggleProtected" dusk="changeProtected">Change Protected</button>
    <button wire:click="showNestedComponent" dusk="showNested">Show Nested</button>

    <h1>
        @unless($changeProtected)
            Protected Content
        @else
            Still Secure Content
        @endunless
    </h1>

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

    protected $fillable = ['banned'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected $rows = [
        [
            'id' => 1,
            'name' => 'First User',
            'email' => 'first@laravel-livewire.com',
            'password' => '',
            'banned' => false,
        ],
        [
            'id' => 2,
            'name' => 'Second user',
            'email' => 'second@laravel-livewire.com',
            'password' => '',
            'banned' => false,
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

class IsBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->banned) {
            return redirect('/force-logout');
        }

        return $next($request);
    }
}
