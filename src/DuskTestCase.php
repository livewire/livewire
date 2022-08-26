<?php

namespace Livewire;

use Throwable;
use Tests\Browser\Security\Component as SecurityComponent;
use Synthetic\SyntheticServiceProvider;
use Sushi\Sushi;
use Psy\Shell;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;
use Orchestra\Testbench\Dusk\Options as DuskOptions;
use Livewire\ServiceProvider;
use Livewire\Livewire;
use Livewire\Drawer\Utils;
use Livewire\Drawer\DuskBrowserMacros;
use Livewire\Component;
use Laravel\Dusk\Browser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Database\Eloquent\Model;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Exception;
use Closure;

class DuskTestCase extends BaseTestCase
{
    public static $useAlpineV3 = false;
    public $applicationTweaks = [];

    public function visit($component, $callback)
    {
        $name = '';

        // If this is an anomymous class...
        File::deleteDirectory($tempDir = sys_get_temp_dir().'/livewire-dusk');

        if (is_object($component) && str(get_class($component))->contains('@anonymous')) {
            $name = 'LivewireAnonymous'.Str::random(5);

            $tempDir = sys_get_temp_dir().'/livewire-dusk';
            mkdir($tempDir);
            $path = $tempDir.'/'.$name.'.php';

            file_put_contents($path, Utils::anonymousClassToStringClass($component, $name));
        } else if (is_object($component)) {
            $name = get_class($component);
        } else {
            $name = $component;
        }

        $url = '/livewire-dusk?component='.$name;

        $this->browse(function ($browser) use ($url, $callback) {
            $callback($browser->visit($url));
        });
    }

    public static function runOnApplicationBoot()
    {
        Route::get('/livewire-dusk', function () {
            $name = request('component');

            return Blade::render(<<<HTML
            <html x-data>
                <head>
                    <script defer src="http://alpine.test/packages/morph/dist/cdn.js"></script>
                    <script defer src="http://alpine.test/packages/alpinejs/dist/cdn.js"></script>
                </head>
                <body>
                    @livewire('$name')

                    @livewireScripts
                </body>
            </html>
            HTML);
        })->middleware('web');

        spl_autoload_register(function ($class) {
            if (str_starts_with($class, 'LivewireAnonymous')) {

                include_once sys_get_temp_dir().'/livewire-dusk/'.$class.'.php';

                return true;
            }

            return false;
        });

        if (File::exists($tempDir = sys_get_temp_dir().'/livewire-dusk')) {
            foreach (File::allFiles($tempDir) as $file) {
                $name = (string) str($file->getFilename())->before('.php');

                Livewire::component($name, new $name);
            }
        }
    }

    public function setUp(): void
    {
        if (isset($_SERVER['CI'])) {
            DuskOptions::withoutUI();
        }

        Browser::mixin(new DuskBrowserMacros);

        $this->afterApplicationCreated(function () {
            $this->makeACleanSlate();
        });

        $this->beforeApplicationDestroyed(function () {
            $this->makeACleanSlate();
        });

        parent::setUp();

        $isUsingAlpineV3 = static::$useAlpineV3;

        $this->tweakApplication(function () use ($isUsingAlpineV3) {
            // Autoload all Livewire components in this test suite.

            // collect(File::allFiles(__DIR__))
            //     ->map(function ($file) {
            //         return 'Tests\\Browser\\'.str($file->getRelativePathname())->before('.php')->replace('/', '\\');
            //     })
            //     ->filter(function ($computedClassName) {
            //         return class_exists($computedClassName);
            //     })
            //     ->filter(function ($class) {
            //         return is_subclass_of($class, Component::class);
            //     })->each(function ($componentClass) {
            //         app('livewire')->component($componentClass);
            //     });

            // @todo...
            // Route::get(
            //     '/livewire-dusk/tests/browser/sync-history-without-mount/{id}',
            //     \Tests\Browser\SyncHistory\ComponentWithMount::class
            // )->middleware('web')->name('sync-history-without-mount');

            // This needs to be registered for Dusk to test the route-parameter binding
            // See: \Tests\Browser\SyncHistory\Test.php
            // Route::get(
            //     '/livewire-dusk/tests/browser/sync-history/{step}',
            //     \Tests\Browser\SyncHistory\Component::class
            // )->middleware('web')->name('sync-history');

            // Route::get(
            //     '/livewire-dusk/tests/browser/sync-history-without-query-string/{step}',
            //     \Tests\Browser\SyncHistory\ComponentWithoutQueryString::class
            // )->middleware('web')->name('sync-history-without-query-string');

            // Route::get(
            //     '/livewire-dusk/tests/browser/sync-history-with-optional-parameter/{step?}',
            //     \Tests\Browser\SyncHistory\ComponentWithOptionalParameter::class
            // )->middleware('web')->name('sync-history-with-optional-parameter');

            // The following two routes belong together. The first one serves a view which in return
            // loads and renders a component dynamically. There may not be a POST route for the first one.
            // Route::get('/livewire-dusk/tests/browser/load-dynamic-component', function () {
            //     return View::file(__DIR__ . '/DynamicComponentLoading/view-load-dynamic-component.blade.php');
            // })->middleware('web')->name('load-dynamic-component');

            // Route::post('/livewire-dusk/tests/browser/dynamic-component', function () {
            //     return View::file(__DIR__ . '/DynamicComponentLoading/view-dynamic-component.blade.php');
            // })->middleware('web')->name('dynamic-component');

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

                return app()->call(new $class);
            })->middleware(['web', 'auth']);

            Gate::policy(Post::class, PostPolicy::class);

            Route::get('/with-authorization/{post}/livewire-dusk/{component}', function (Post $post, $component) {
                $class = urldecode($component);

                return app()->call(new $class);
            })->middleware(['web', 'auth', 'can:update,post']);

            Route::middleware('web')->get('/entangle-turbo', function () {
                return view('turbo', [
                    'link' => '/livewire-dusk/' . urlencode(\Tests\Browser\Alpine\Entangle\ToggleEntangledTurbo::class),
                ]);
            })->name('entangle-turbo');

            app('session')->put('_token', 'this-is-a-hack-because-something-about-validating-the-csrf-token-is-broken');

            app('config')->set('view.paths', [
                __DIR__.'/views',
                resource_path('views'),
            ]);

            config()->set('app.debug', true);

            // @todo...
            // Livewire::addPersistentMiddleware(AllowListedMiddleware::class);

            app('config')->set('use_alpine_v3', $isUsingAlpineV3);
        });
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($tempDir = sys_get_temp_dir().'/livewire-dusk');

        $this->removeApplicationTweaks();

        parent::tearDown();
    }

    // We don't want to deal with screenshots or console logs.
    protected function storeConsoleLogsFor($browsers) {}
    protected function captureFailuresFor($browsers) {}

    public function makeACleanSlate()
    {
        Artisan::call('view:clear');

        File::deleteDirectory($this->livewireViewsPath());
        File::cleanDirectory(__DIR__.'/downloads');
        File::deleteDirectory($this->livewireClassesPath());
        File::delete(app()->bootstrapPath('cache/livewire-components.php'));
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            SyntheticServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('view.paths', [
            __DIR__.'/views',
            resource_path('views'),
        ]);

        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('filesystems.disks.dusk-downloads', [
            'driver' => 'local',
            'root' => __DIR__.'/downloads',
        ]);
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', HttpKernel::class);
    }

    protected function livewireClassesPath($path = '')
    {
        return app_path('Http/Livewire'.($path ? '/'.$path : ''));
    }

    protected function livewireViewsPath($path = '')
    {
        return resource_path('views').'/livewire'.($path ? '/'.$path : '');
    }

    protected function driver(): RemoteWebDriver
    {
        $options = DuskOptions::getChromeOptions();

        $options->setExperimentalOption('prefs', [
            'download.default_directory' => __DIR__.'/downloads',
        ]);

        return RemoteWebDriver::create(
                'http://localhost:9515',
                DesiredCapabilities::chrome()->setCapability(
                    ChromeOptions::CAPABILITY,
                    $options
                )
            );
    }

    public function browse(Closure $callback)
    {
        parent::browse(function (...$browsers) use ($callback) {
            try {
                $callback(...$browsers);
            } catch (Exception $e) {
                if (DuskOptions::hasUI()) $this->breakIntoATinkerShell($browsers, $e);

                throw $e;
            } catch (Throwable $e) {
                if (DuskOptions::hasUI()) $this->breakIntoATinkerShell($browsers, $e);

                throw $e;
            }
        });
    }

    public function breakIntoATinkerShell($browsers, $e)
    {
        $sh = new Shell();

        $sh->add(new DuskCommand($this, $e));

        $sh->setScopeVariables([
            'browsers' => $browsers,
        ]);

        $sh->addInput('dusk');

        $sh->setBoundObject($this);

        $sh->run();

        return $sh->getScopeVariables(false);
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

use Psy\Command\Command;
use Psy\Output\ShellOutput;
use Psy\Formatter\CodeFormatter;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DuskCommand extends Command
{
    public $e;
    public $testCase;

    public function __construct($testCase, $e, $colorMode = null)
    {
        $this->e = $e;
        $this->testCase = $testCase;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('dusk');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = (new ReflectionClass($this->testCase))->getFilename();

        $line = collect($this->e->getTrace())
            ->first(function ($entry) use ($file) {
                return ($entry['file'] ?? '') === $file;
            })['line'];

        $info = [
            'file' => $file,
            'line' => $line,
        ];

        $num       = 2;
        $lineNum   = $info['line'];
        $startLine = max($lineNum - $num, 1);
        $endLine   = $lineNum + $num;
        $code      = file_get_contents($info['file']);

        if ($output instanceof ShellOutput) {
            $output->startPaging();
        }

        $output->writeln(sprintf('From <info>%s:%s</info>:', $this->replaceCwd($info['file']), $lineNum));
        $output->write(CodeFormatter::formatCode($code, $startLine, $endLine, $lineNum), false);

        $output->writeln("\n".$this->e->getMessage());

        if ($output instanceof ShellOutput) {
            $output->stopPaging();
        }

        return 0;
    }

    private function replaceCwd($file)
    {
        $cwd = getcwd();
        if ($cwd === false) {
            return $file;
        }

        $cwd = rtrim($cwd, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return preg_replace('/^' . preg_quote($cwd, '/') . '/', '', $file);
    }
}

use Illuminate\Foundation\Http\Kernel;

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
