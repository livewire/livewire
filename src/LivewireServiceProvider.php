<?php

namespace Livewire;

use Illuminate\View\View;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireViewCompilerEngine;
use Livewire\Controllers\FileUploadHandler;
use Livewire\Controllers\FilePreviewHandler;
use Livewire\Controllers\HttpConnectionHandler;
use Livewire\Controllers\LivewireJavaScriptAssets;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Livewire\Commands\{
    CpCommand,
    MvCommand,
    RmCommand,
    CopyCommand,
    MakeCommand,
    MoveCommand,
    StubsCommand,
    TouchCommand,
    DeleteCommand,
    ComponentParser,
    DiscoverCommand,
    S3CleanupCommand,
    MakeLivewireCommand
};
use Livewire\HydrationMiddleware\{
    RenderView,
    PersistErrorBag,
    PerformActionCalls,
    CallHydrationHooks,
    CastPublicProperties,
    PerformEventEmissions,
    HydratePublicProperties,
    PerformDataBindingUpdates,
    SecureHydrationWithChecksum,
    HashPropertiesForDirtyDetection,
    HydratePreviouslyRenderedChildren,
    HydrateEloquentModelsAsPublicProperties,
    HydratePropertiesWithCustomRuntimeHydrators
};
use Livewire\Macros\ViewMacros;

class LivewireServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerTestMacros();
        $this->registerLivewireSingleton();
        $this->registerComponentAutoDiscovery();
    }

    public function boot()
    {
        $this->registerViews();
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerRenameMes();
        $this->registerTestMacros();
        $this->registerViewMacros();
        $this->registerTagCompiler();
        $this->registerPublishables();
        $this->registerBladeDirectives();
        $this->registerViewCompilerEngine();
        $this->registerHydrationMiddleware();

        // Bypass specific middlewares during Livewire requests.
        // These are usually helpful during a typical request, but
        // during Livewire requests, they can damage data properties.
        $this->bypassTheseMiddlewaresDuringLivewireRequests([
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            // If the app overrode "TrimStrings".
            \App\Http\Middleware\TrimStrings::class,
        ]);
    }

    protected function registerLivewireSingleton()
    {
        $this->app->singleton('livewire', LivewireManager::class);
    }

    protected function registerComponentAutoDiscovery()
    {
        // Rather than forcing users to register each individual component,
        // we will auto-detect the component's class based on its kebab-cased
        // alias. For instance: 'examples.foo' => App\Http\Livewire\Examples\Foo

        // We will generate a manifest file so we don't have to do the lookup every time.
        $defaultManifestPath = $this->app['livewire']->isOnVapor()
            ? '/tmp/storage/bootstrap/cache/livewire-components.php'
            : app()->bootstrapPath('cache/livewire-components.php');

        $this->app->singleton(LivewireComponentsFinder::class, function () use ($defaultManifestPath) {
            return new LivewireComponentsFinder(
                new Filesystem,
                config('livewire.manifest_path') ?: $defaultManifestPath,
                ComponentParser::generatePathFromNamespace(
                    config('livewire.class_namespace', 'App\\Http\\Livewire')
                )
            );
        });
    }

    protected function registerViews()
    {
        // This is mainly for overriding Laravel's pagination views
        // when a user applies the WithPagination trait to a component.
        $this->loadViewsFrom(
            __DIR__.DIRECTORY_SEPARATOR.'views',
            'livewire'
        );
    }

    protected function registerRoutes()
    {
        if (! $this->app->environment('production')) {
            RouteFacade::get('/livewire-dusk/{component}', function ($component) {
                $class = urldecode($component);

                return app()->call(new $class);
            })->middleware('web');
        }

        RouteFacade::get('/livewire/livewire.js', [LivewireJavaScriptAssets::class, 'source']);
        RouteFacade::get('/livewire/livewire.js.map', [LivewireJavaScriptAssets::class, 'maps']);

        RouteFacade::post('/livewire/message/{name}', HttpConnectionHandler::class)
            ->middleware(config('livewire.middleware_group', 'web'));

        RouteFacade::post('/livewire/upload-file', [FileUploadHandler::class, 'handle'])
            ->middleware(config('livewire.middleware_group', 'web'))
            ->name('livewire.upload-file');

        RouteFacade::get('/livewire/preview-file/{filename}', [FilePreviewHandler::class, 'handle'])
            ->middleware(config('livewire.middleware_group', 'web'))
            ->name('livewire.preview-file');
    }

    protected function registerCommands()
    {
        if (! $this->app->runningInConsole()) return;

        $this->commands([
            MakeLivewireCommand::class, // make:livewire
            MakeCommand::class,         // livewire:make
            TouchCommand::class,        // livewire:touch
            CopyCommand::class,         // livewire:copy
            CpCommand::class,           // livewire:cp
            DeleteCommand::class,       // livewire:delete
            RmCommand::class,           // livewire:rm
            MoveCommand::class,         // livewire:move
            MvCommand::class,           // livewire:mv
            StubsCommand::class,        // livewire:stubs
            DiscoverCommand::class,     // livewire:discover
            S3CleanupCommand::class,    // livewire:configure-s3-upload-cleanup
        ]);
    }

    protected function registerTestMacros()
    {
        // Usage: $this->assertSeeLivewire('counter');
        $macro = function ($component) {
            $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

            \PHPUnit\Framework\Assert::assertStringContainsString(
                (string) $escapedComponentName, $this->getContent(),
                'Cannot find Livewire component ['.$component.'] rendered on page.'
            );

            return $this;
        };


        TestResponse::macro('assertSeeLivewire', $macro);

        // Usage: $this->assertDontSeeLivewire('counter');
        $macro = function ($component) {
            $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

            \PHPUnit\Framework\Assert::assertStringNotContainsString(
                (string) $escapedComponentName, $this->getContent(),
                'Found Livewire component ['.$component.'] rendered on page.'
            );

            return $this;
        };


        TestResponse::macro('assertDontSeeLivewire', $macro);
    }

    protected function registerViewMacros()
    {
        View::mixin(new ViewMacros);
    }

    protected function registerTagCompiler()
    {
        if (method_exists($this->app['blade.compiler'], 'precompiler')) {
            $this->app['blade.compiler']->precompiler(function ($string) {
                return app(LivewireTagCompiler::class)->compile($string);
            });
        }
    }

    protected function registerPublishables()
    {
        $this->publishesToGroups([
            __DIR__.'/../dist' => public_path('vendor/livewire'),
        ], ['livewire', 'livewire:assets']);

        $this->publishesToGroups([
            __DIR__.'/../config/livewire.php' => base_path('config/livewire.php'),
        ], ['livewire', 'livewire:config']);
    }

    protected function registerBladeDirectives()
    {
        Blade::directive('this', [LivewireBladeDirectives::class, 'this']);
        Blade::directive('entangle', [LivewireBladeDirectives::class, 'entangle']);
        Blade::directive('livewire', [LivewireBladeDirectives::class, 'livewire']);
        Blade::directive('livewireStyles', [LivewireBladeDirectives::class, 'livewireStyles']);
        Blade::directive('livewireScripts', [LivewireBladeDirectives::class, 'livewireScripts']);
    }

    protected function registerViewCompilerEngine()
    {
        // This is a custom view engine that gets used when rendering
        // Livewire views. Things like letting certain exceptions bubble
        // to the handler, and registering custom directives like: "@this".
        $this->app->make('view.engine.resolver')->register('blade', function () {
            return new LivewireViewCompilerEngine($this->app['blade.compiler']);
        });
    }

    protected function registerRenameMes()
    {
        RenameMe\SupportEvents::init();
        RenameMe\SupportLocales::init();
        RenameMe\SupportDateTimes::init();
        RenameMe\SupportRedirects::init();
        RenameMe\SupportQueryString::init();
        RenameMe\SupportCollections::init();
        RenameMe\OptimizeRenderedDom::init();
        RenameMe\SupportFileDownloads::init();
        RenameMe\SupportActionReturns::init();
    }

    protected function registerHydrationMiddleware()
    {
        Livewire::registerHydrationMiddleware([
        /* This is the core middleware stack of Livewire. It's important */
        /* to understand that the request goes through each class by the */
        /* order it is listed in this array, and is reversed on response */
        /*                                                               */
        /* Incoming Request                            Outgoing Response */
        /* v */ SecureHydrationWithChecksum::class,                 /* ^ */
        /* v */ HydratePublicProperties::class,                     /* ^ */
        /* v */ HydrateEloquentModelsAsPublicProperties::class,     /* ^ */
        /* v */ HashPropertiesForDirtyDetection::class,             /* ^ */
        /* v */ CallHydrationHooks::class,                          /* ^ */
        /* v */ PersistErrorBag::class,                             /* ^ */
        /* v */ PerformDataBindingUpdates::class,                   /* ^ */
        /* v */ HydratePropertiesWithCustomRuntimeHydrators::class, /* ^ */
        /* v */ CastPublicProperties::class,                        /* ^ */
        /* v */ HydratePreviouslyRenderedChildren::class,           /* ^ */
        /* v */ PerformActionCalls::class,                          /* ^ */
        /* v */ PerformEventEmissions::class,                       /* ^ */
        /* v */ RenderView::class,                                  /* ^ */
        ]);

        Livewire::registerInitialDehydrationMiddleware([
        /* Initial Response */
        /* ^ */ [SecureHydrationWithChecksum::class, 'dehydrate'],
        /* ^ */ [HydratePreviouslyRenderedChildren::class, 'dehydrate'],
        /* ^ */ [HydratePublicProperties::class, 'dehydrate'],
        /* ^ */ [CallHydrationHooks::class, 'initialDehydrate'],
        /* ^ */ [HydrateEloquentModelsAsPublicProperties::class, 'dehydrate'],
        /* ^ */ [HydratePropertiesWithCustomRuntimeHydrators::class, 'dehydrate'],
        /* ^ */ [PersistErrorBag::class, 'dehydrate'],
        /* ^ */ [CastPublicProperties::class, 'dehydrate'],
        /* ^ */ [RenderView::class, 'dehydrate'],
        ]);

        Livewire::registerInitialHydrationMiddleware([
            [CallHydrationHooks::class, 'initialHydrate'],
        ]);
    }

    protected function bypassTheseMiddlewaresDuringLivewireRequests(array $middlewareToExclude)
    {
        if (! $this->app['livewire']->isLivewireRequest()) return;

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
