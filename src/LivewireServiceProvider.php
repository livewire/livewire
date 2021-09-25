<?php

namespace Livewire;

use Illuminate\View\View;
use Illuminate\Testing\TestView;
use Illuminate\Testing\TestResponse;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Controllers\FileUploadHandler;
use Livewire\Controllers\FilePreviewHandler;
use Livewire\Controllers\HttpConnectionHandler;
use Livewire\Controllers\LivewireJavaScriptAssets;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
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
    PublishCommand,
    ComponentParser,
    DiscoverCommand,
    S3CleanupCommand,
    MakeLivewireCommand,
};
use Livewire\Macros\ViewMacros;
use Livewire\HydrationMiddleware\{
    RenderView,
    PerformActionCalls,
    CallHydrationHooks,
    PerformEventEmissions,
    HydratePublicProperties,
    PerformDataBindingUpdates,
    CallPropertyHydrationHooks,
    SecureHydrationWithChecksum,
    HashDataPropertiesForDirtyDetection,
    NormalizeServerMemoSansDataForJavaScript,
    NormalizeComponentPropertiesForJavaScript,
};

class LivewireServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerConfig();
        $this->registerTestMacros();
        $this->registerLivewireSingleton();
        $this->registerComponentAutoDiscovery();
    }

    public function boot()
    {
        $this->registerViews();
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerFeatures();
        $this->registerViewMacros();
        $this->registerTagCompiler();
        $this->registerPublishables();
        $this->registerBladeDirectives();
        $this->registerViewCompilerEngine();
        $this->registerHydrationMiddleware();
        $this->registerDisableBrowserCacheMiddleware();

        // Bypass specific middlewares during Livewire requests.
        // These are usually helpful during a typical request, but
        // during Livewire requests, they can damage data properties.
        if (! $this->attemptToBypassRequestModifyingMiddlewareViaCallbacks()) {
            $this->bypassTheseMiddlewaresDuringLivewireRequests([
                TrimStrings::class,
                ConvertEmptyStringsToNull::class,
                // If the app overrode "TrimStrings".
                \App\Http\Middleware\TrimStrings::class,
            ]);
        }   
    }

    protected function registerLivewireSingleton()
    {
        $this->app->singleton(LivewireManager::class);

        $this->app->alias(LivewireManager::class, 'livewire');
    }

    protected function registerComponentAutoDiscovery()
    {
        // Rather than forcing users to register each individual component,
        // we will auto-detect the component's class based on its kebab-cased
        // alias. For instance: 'examples.foo' => App\Http\Livewire\Examples\Foo

        // We will generate a manifest file so we don't have to do the lookup every time.
        $defaultManifestPath = $this->app['livewire']->isRunningServerless()
            ? '/tmp/storage/bootstrap/cache/livewire-components.php'
            : app()->bootstrapPath('cache/livewire-components.php');

        $this->app->singleton(LivewireComponentsFinder::class, function () use ($defaultManifestPath) {
            return new LivewireComponentsFinder(
                new Filesystem,
                config('livewire.manifest_path') ?: $defaultManifestPath,
                ComponentParser::generatePathFromNamespace(
                    config('livewire.class_namespace')
                )
            );
        });
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/livewire.php', 'livewire');
    }

    protected function registerViews()
    {
        // This is mainly for overriding Laravel's pagination views
        // when a user applies the WithPagination trait to a component.
        $this->loadViewsFrom(
            __DIR__.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pagination',
            'livewire'
        );
    }

    protected function registerRoutes()
    {
        RouteFacade::post('/livewire/message/{name}', HttpConnectionHandler::class)
            ->name('livewire.message')
            ->middleware(config('livewire.middleware_group', ''));

        RouteFacade::post('/livewire/upload-file', [FileUploadHandler::class, 'handle'])
            ->name('livewire.upload-file')
            ->middleware(config('livewire.middleware_group', ''));

        RouteFacade::get('/livewire/preview-file/{filename}', [FilePreviewHandler::class, 'handle'])
            ->name('livewire.preview-file')
            ->middleware(config('livewire.middleware_group', ''));

        RouteFacade::get('/livewire/livewire.js', [LivewireJavaScriptAssets::class, 'source']);
        RouteFacade::get('/livewire/livewire.js.map', [LivewireJavaScriptAssets::class, 'maps']);
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
            PublishCommand::class,      // livewire:publish
        ]);
    }

    protected function registerTestMacros()
    {
        // Usage: $this->assertSeeLivewire('counter');
        TestResponse::macro('assertSeeLivewire', function ($component) {
            if (is_subclass_of($component, Component::class)) {
                $component = $component::getName();
            }

            $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

            \PHPUnit\Framework\Assert::assertStringContainsString(
                $escapedComponentName,
                $this->getContent(),
                'Cannot find Livewire component ['.$component.'] rendered on page.'
            );

            return $this;
        });

        // Usage: $this->assertDontSeeLivewire('counter');
        TestResponse::macro('assertDontSeeLivewire', function ($component) {
            if (is_subclass_of($component, Component::class)) {
                $component = $component::getName();
            }

            $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

            \PHPUnit\Framework\Assert::assertStringNotContainsString(
                $escapedComponentName,
                $this->getContent(),
                'Found Livewire component ['.$component.'] rendered on page.'
            );

            return $this;
        });

        if (class_exists(TestView::class)) {
            TestView::macro('assertSeeLivewire', function ($component) {
                if (is_subclass_of($component, Component::class)) {
                    $component = $component::getName();
                }

                $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

                \PHPUnit\Framework\Assert::assertStringContainsString(
                    $escapedComponentName,
                    $this->rendered,
                    'Cannot find Livewire component ['.$component.'] rendered on page.'
                );

                return $this;
            });

            TestView::macro('assertDontSeeLivewire', function ($component) {
                if (is_subclass_of($component, Component::class)) {
                    $component = $component::getName();
                }

                $escapedComponentName = trim(htmlspecialchars(json_encode(['name' => $component])), '{}');

                \PHPUnit\Framework\Assert::assertStringNotContainsString(
                    $escapedComponentName,
                    $this->rendered,
                    'Found Livewire component ['.$component.'] rendered on page.'
                );

                return $this;
            });
        }
    }

    protected function registerViewMacros()
    {
        // Early versions of Laravel 7.x don't have this method.
        if (method_exists(ComponentAttributeBag::class, 'macro')) {
            ComponentAttributeBag::macro('wire', function ($name) {
                $entries = head($this->whereStartsWith('wire:'.$name));

                $directive = head(array_keys($entries));
                $value = head(array_values($entries));

                return new WireDirective($name, $directive, $value);
            });
        }

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

        $this->publishesToGroups([
            __DIR__.'/views/pagination' => $this->app->resourcePath('views/vendor/livewire'),
        ], ['livewire', 'livewire:pagination']);
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

            // If the application is using Ignition, make sure Livewire's view compiler
            // uses a version that extends Ignition's so it can continue to report errors
            // correctly. Don't change this class without first submitting a PR to Ignition.
            if (class_exists('Facade\Ignition\IgnitionServiceProvider')) {
                return new CompilerEngineForIgnition($this->app['blade.compiler']);
            }

            return new LivewireViewCompilerEngine($this->app['blade.compiler']);
        });
    }

    protected function registerFeatures()
    {
        Features\SupportEvents::init();
        Features\SupportLocales::init();
        Features\SupportChildren::init();
        Features\SupportRedirects::init();
        Features\SupportValidation::init();
        Features\SupportBootMethod::init();
        Features\SupportFileUploads::init();
        Features\OptimizeRenderedDom::init();
        Features\SupportFileDownloads::init();
        Features\SupportActionReturns::init();
        Features\SupportBrowserHistory::init();
        Features\SupportComponentTraits::init();
        Features\SupportRootElementTracking::init();
    }

    protected function registerHydrationMiddleware()
    {
        LifecycleManager::registerHydrationMiddleware([

            /* This is the core middleware stack of Livewire. It's important */
            /* to understand that the request goes through each class by the */
            /* order it is listed in this array, and is reversed on response */
            /*                                                               */
            /* ↓    Incoming Request                  Outgoing Response    ↑ */
            /* ↓                                                           ↑ */
            /* ↓    Secure Stuff                                           ↑ */
            /* ↓ */ SecureHydrationWithChecksum::class, /* --------------- ↑ */
            /* ↓ */ NormalizeServerMemoSansDataForJavaScript::class, /* -- ↑ */
            /* ↓ */ HashDataPropertiesForDirtyDetection::class, /* ------- ↑ */
            /* ↓                                                           ↑ */
            /* ↓    Hydrate Stuff                                          ↑ */
            /* ↓ */ HydratePublicProperties::class, /* ------------------- ↑ */
            /* ↓ */ CallPropertyHydrationHooks::class, /* ---------------- ↑ */
            /* ↓ */ CallHydrationHooks::class, /* ------------------------ ↑ */
            /* ↓                                                           ↑ */
            /* ↓    Update Stuff                                           ↑ */
            /* ↓ */ PerformDataBindingUpdates::class, /* ----------------- ↑ */
            /* ↓ */ PerformActionCalls::class, /* ------------------------ ↑ */
            /* ↓ */ PerformEventEmissions::class, /* --------------------- ↑ */
            /* ↓                                                           ↑ */
            /* ↓    Output Stuff                                           ↑ */
            /* ↓ */ RenderView::class, /* -------------------------------- ↑ */
            /* ↓ */ NormalizeComponentPropertiesForJavaScript::class, /* - ↑ */

        ]);

        LifecycleManager::registerInitialDehydrationMiddleware([

            /* Initial Response */
            /* ↑ */ [SecureHydrationWithChecksum::class, 'dehydrate'],
            /* ↑ */ [NormalizeServerMemoSansDataForJavaScript::class, 'dehydrate'],
            /* ↑ */ [HydratePublicProperties::class, 'dehydrate'],
            /* ↑ */ [CallPropertyHydrationHooks::class, 'dehydrate'],
            /* ↑ */ [CallHydrationHooks::class, 'initialDehydrate'],
            /* ↑ */ [RenderView::class, 'dehydrate'],
            /* ↑ */ [NormalizeComponentPropertiesForJavaScript::class, 'dehydrate'],

        ]);

        LifecycleManager::registerInitialHydrationMiddleware([

                [CallHydrationHooks::class, 'initialHydrate'],

        ]);
    }

    protected function registerDisableBrowserCacheMiddleware()
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

        if ($kernel->hasMiddleware(DisableBrowserCache::class)) {
            return;
        }

        $kernel->pushMiddleware(DisableBrowserCache::class);
    }

    protected function attemptToBypassRequestModifyingMiddlewareViaCallbacks()
    {
        if (method_exists(TrimStrings::class, 'skipWhen') &&
            method_exists(ConvertEmptyStringsToNull::class, 'skipWhen')) {
            TrimStrings::skipWhen(function () {
                return Livewire::isProbablyLivewireRequest();
            });

            ConvertEmptyStringsToNull::skipWhen(function () {
                return Livewire::isProbablyLivewireRequest();
            });

            return true;
        }

        return false;
    }

    protected function bypassTheseMiddlewaresDuringLivewireRequests(array $middlewareToExclude)
    {
        if (! Livewire::isProbablyLivewireRequest()) return;

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
