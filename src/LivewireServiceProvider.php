<?php

namespace Livewire;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Livewire\Macros\RouteMacros;
use Livewire\Macros\RouterMacros;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireViewCompilerEngine;
use Livewire\Connection\HttpConnectionHandler;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Testing\TestResponse as Laravel7TestResponse;
use Livewire\Commands\{
    ComponentParser,
    CopyCommand,
    CpCommand,
    DeleteCommand,
    DiscoverCommand,
    MakeCommand,
    MakeLivewireCommand,
    MoveCommand,
    MvCommand,
    RmCommand,
    StubCommand,
    TouchCommand
};
use Livewire\HydrationMiddleware\{
    UpdateQueryString,
    ClearFlashMessagesIfNotRedirectingAway,
    ForwardPrefetch,
    HashPropertiesForDirtyDetection,
    HydratePreviouslyRenderedChildren,
    HydratePublicProperties,
    IncludeIdAsRootTagAttribute,
    InterceptRedirects,
    PersistErrorBag,
    PrioritizeDataUpdatesBeforeActionCalls,
    RegisterEmittedEvents,
    RegisterEventsBeingListenedFor,
    SecureHydrationWithChecksum
};

class LivewireServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('livewire', LivewireManager::class);

        $this->app->singleton(LivewireComponentsFinder::class, function () {
            $isHostedOnVapor = ($_ENV['SERVER_SOFTWARE'] ?? null) === 'vapor';

            $defaultManifestPath = $isHostedOnVapor
                ? '/tmp/storage/bootstrap/cache/livewire-components.php'
                : app()->bootstrapPath('cache/livewire-components.php');

            return new LivewireComponentsFinder(
                new Filesystem,
                config('livewire.manifest_path') ?? $defaultManifestPath,
                ComponentParser::generatePathFromNamespace(config('livewire.class_namespace', 'App\\Http\\Livewire'))
            );
        });
    }

    public function boot()
    {
        if ($this->app['livewire']->isLivewireRequest()) {
            $this->bypassMiddleware([
                TrimStrings::class,
                // In case the user has over-rode "TrimStrings"
                \App\Http\Middleware\TrimStrings::class,
                ConvertEmptyStringsToNull::class,
            ]);
        }

        $this->registerViews();
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerTestMacros();
        $this->registerRouteMacros();
        $this->registerPublishables();
        $this->registerBladeDirectives();
        $this->registerViewCompilerEngine();
        $this->registerHydrationMiddleware();
    }

    public function registerViews()
    {
        // This is for Livewire's pagination views.
        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'views', config('livewire.view-path', 'livewire'));
    }

    public function registerRoutes()
    {
        RouteFacade::get('/livewire/livewire.js', [LivewireJavaScriptAssets::class, 'source']);
        RouteFacade::get('/livewire/livewire.js.map', [LivewireJavaScriptAssets::class, 'maps']);

        RouteFacade::post('/livewire/message/{name}', HttpConnectionHandler::class)
            ->middleware(config('livewire.middleware_group', 'web'));
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CopyCommand::class,
                CpCommand::class,
                DeleteCommand::class,
                DiscoverCommand::class,
                MakeCommand::class,
                MakeLivewireCommand::class,
                MoveCommand::class,
                MvCommand::class,
                RmCommand::class,
                TouchCommand::class,
                StubCommand::class,
            ]);
        }
    }

    public function registerTestMacros()
    {
        $macro = function ($component) {
            $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

            \PHPUnit\Framework\Assert::assertStringContainsString(
                (string) $escapedComponentName,
                $this->getContent(),
                'Cannot find Livewire component ['.$component.'] rendered on page.'
            );

            return $this;
        };

        if (Application::VERSION === '7.x-dev' || version_compare(Application::VERSION, '7.0', '>=')) {
            Laravel7TestResponse::macro('assertSeeLivewire', $macro);
        } else {
            TestResponse::macro('assertSeeLivewire', $macro);
        }
    }

    public function registerRouteMacros()
    {
        Route::mixin(new RouteMacros);
        Router::mixin(new RouterMacros);
    }

    protected function registerPublishables()
    {
        $this->publishesToGroups([
            __DIR__.'/../config/livewire.php' => base_path('config/livewire.php'),
        ], ['livewire', 'livewire:config']);

        $this->publishesToGroups([
            __DIR__.'/../dist' => public_path('vendor/livewire'),
        ], ['livewire', 'livewire:assets']);
    }

    public function registerBladeDirectives()
    {
        // @todo: removing in 1.0
        Blade::directive('livewireAssets', [LivewireBladeDirectives::class, 'livewireAssets']);
        Blade::directive('livewireStyles', [LivewireBladeDirectives::class, 'livewireStyles']);
        Blade::directive('livewireScripts', [LivewireBladeDirectives::class, 'livewireScripts']);
        Blade::directive('livewire', [LivewireBladeDirectives::class, 'livewire']);
    }

    protected function registerViewCompilerEngine()
    {
        $this->app->make('view.engine.resolver')->register('blade', function () {
            return new LivewireViewCompilerEngine($this->app['blade.compiler']);
        });
    }

    public function registerHydrationMiddleware()
    {
        Livewire::registerInitialHydrationMiddleware([
            [InterceptRedirects::class, 'hydrate'],
        ]);

        Livewire::registerInitialDehydrationMiddleware([
            [PersistErrorBag::class, 'dehydrate'],
            [RegisterEventsBeingListenedFor::class, 'dehydrate'],
            [RegisterEmittedEvents::class, 'dehydrate'],
            [HydratePublicProperties::class, 'dehydrate'],
            [HydratePreviouslyRenderedChildren::class, 'dehydrate'],
            [SecureHydrationWithChecksum::class, 'dehydrate'],
            [IncludeIdAsRootTagAttribute::class, 'dehydrate'],
            [InterceptRedirects::class, 'dehydrate'],
        ]);

        Livewire::registerHydrationMiddleware([
            IncludeIdAsRootTagAttribute::class,
            ClearFlashMessagesIfNotRedirectingAway::class,
            SecureHydrationWithChecksum::class,
            RegisterEventsBeingListenedFor::class,
            RegisterEmittedEvents::class,
            PersistErrorBag::class,
            HydratePublicProperties::class,
            HydratePreviouslyRenderedChildren::class,
            HashPropertiesForDirtyDetection::class,
            InterceptRedirects::class,
            PrioritizeDataUpdatesBeforeActionCalls::class,
            ForwardPrefetch::class,
            UpdateQueryString::class,
        ]);
    }

    protected function bypassMiddleware(array $middlewareToExclude)
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

        $openKernel = new ObjectPrybar($kernel);

        $middleware = $openKernel->getProperty('middleware');

        $openKernel->setProperty('middleware', array_diff($middleware, $middlewareToExclude));
    }

    protected function publishesToGroups(array $paths, $groups = null)
    {
        if (is_null($groups)) {
            $this->publishes($paths);

            return;
        }

        foreach ((array) $groups as $group) {
            $this->publishes($paths, $group);
        }
    }
}
