<?php

namespace Livewire;
use Livewire\Finder\Finder;
use Livewire\Factory\Factory;
use Livewire\Compiler\Compiler;
use Illuminate\Foundation\Console\AboutCommand;
use Composer\InstalledVersions;
use Livewire\Compiler\CacheManager;

class LivewireServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->registerLivewireServices();
        $this->registerConfig();
        $this->bootEventBus();
        $this->registerMechanisms();
    }

    public function boot()
    {
        $this->bootConfig();
        $this->bootMechanisms();
        $this->bootFeatures();
    }

    protected function registerLivewireServices()
    {
        $this->app->alias(LivewireManager::class, 'livewire');
        $this->app->singleton(LivewireManager::class);

        app('livewire')->setProvider($this);

        $this->app->singleton('livewire.finder', function () {
            $finder = new Finder;

            $finder->addLocation(classNamespace: config('livewire.class_namespace'));

            return $finder;
        });

        $this->app->singleton('livewire.compiler', function () {
            return new Compiler(
                new CacheManager(
                    storage_path('framework/views/livewire')
                )
            );
        });

        $this->app->scoped('livewire.factory', function ($app) {
            return new Factory(
                $app['livewire.finder'],
                $app['livewire.compiler']
            );
        });
    }

    protected function registerConfig()
    {
        $config = __DIR__.'/../config/livewire.php';

        $this->publishes([$config => base_path('config/livewire.php')], ['livewire', 'livewire:config']);

        $this->mergeConfigFrom($config, 'livewire');
    }

    protected function bootEventBus()
    {
        app(EventBus::class)->boot();
    }

    protected function getMechanisms()
    {
        return [
            Mechanisms\PersistentMiddleware\PersistentMiddleware::class,
            Mechanisms\HandleComponents\HandleComponents::class,
            Mechanisms\HandleRequests\HandleRequests::class,
            Mechanisms\FrontendAssets\FrontendAssets::class,
            Mechanisms\ExtendBlade\ExtendBlade::class,
            Mechanisms\CompileLivewireTags\CompileLivewireTags::class,
            Mechanisms\ClearCachedFiles::class,
            Mechanisms\RenderComponent::class,
            Mechanisms\DataStore::class,
        ];
    }

    protected function registerMechanisms()
    {
        foreach ($this->getMechanisms() as $mechanism) {
            app($mechanism)->register();
        }
    }

    protected function bootConfig()
    {
        // Adapt v4 config to v3 config...

        config()->set(
            'livewire.component_locations',
            config('livewire.component_locations', [
                resource_path('views/components'),
                resource_path('views/livewire'),
            ])
        );

        config()->set(
            'livewire.component_layout',
            config('livewire.component_layout', config('livewire.layout', null))
        );

        config()->set(
            'livewire.component_placeholder',
            config('livewire.component_placeholder', config('livewire.lazy_placeholder', null))
        );

        config()->set(
            'livewire.make_command',
            config('livewire.make_command', [
                'type' => 'class',
                'emoji' => false,
            ])
        );

        // Register view-based component locations and namespaces...

        foreach (config('livewire.component_locations', []) as $location) {
            app('livewire.finder')->addLocation(viewPath: $location);

            if (! is_dir($location)) continue;

            app('blade.compiler')->anonymousComponentPath($location);
            app('view')->addLocation($location);
        }

        foreach (config('livewire.component_namespaces', []) as $namespace => $location) {
            app('livewire.finder')->addNamespace($namespace, viewPath: $location);

            if (! is_dir($location)) continue;

            app('blade.compiler')->anonymousComponentPath($location, $namespace);
            app('view')->addNamespace($namespace, $location);
        }
    }

    protected function bootMechanisms()
    {
        if (class_exists(AboutCommand::class) && class_exists(InstalledVersions::class)) {
            AboutCommand::add('Livewire', [
                'Livewire' => InstalledVersions::getPrettyVersion('livewire/livewire'),
            ]);
        }

        foreach ($this->getMechanisms() as $mechanism) {
            app($mechanism)->boot();
        }
    }

    protected function bootFeatures()
    {
        foreach([
            Features\SupportWireModelingNestedComponents\SupportWireModelingNestedComponents::class,
            Features\SupportMultipleRootElementDetection\SupportMultipleRootElementDetection::class,
            Features\SupportMorphAwareBladeCompilation\SupportMorphAwareBladeCompilation::class,
            Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache::class,
            Features\SupportNestedComponentListeners\SupportNestedComponentListeners::class,
            Features\SupportHtmlAttributeForwarding\SupportHtmlAttributeForwarding::class,
            Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::class,
            Features\SupportComputed\SupportLegacyComputedPropertySyntax::class,
            Features\SupportNestingComponents\SupportNestingComponents::class,
            Features\SupportCompiledWireKeys\SupportCompiledWireKeys::class,
            Features\SupportScriptsAndAssets\SupportScriptsAndAssets::class,
            Features\SupportBladeAttributes\SupportBladeAttributes::class,
            Features\SupportConsoleCommands\SupportConsoleCommands::class,
            Features\SupportPageComponents\SupportPageComponents::class,
            Features\SupportReactiveProps\SupportReactiveProps::class,
            Features\SupportReleaseTokens\SupportReleaseTokens::class,
            Features\SupportFileDownloads\SupportFileDownloads::class,
            Features\SupportJsEvaluation\SupportJsEvaluation::class,
            Features\SupportMagicActions\SupportMagicActions::class,
            Features\SupportQueryString\SupportQueryString::class,
            Features\SupportFileUploads\SupportFileUploads::class,
            Features\SupportTeleporting\SupportTeleporting::class,
            Features\SupportLazyLoading\SupportLazyLoading::class,
            Features\SupportFormObjects\SupportFormObjects::class,
            Features\SupportAttributes\SupportAttributes::class,
            Features\SupportPagination\SupportPagination::class,
            Features\SupportValidation\SupportValidation::class,
            Features\SupportWithMethod\SupportWithMethod::class,
            Features\SupportIsolating\SupportIsolating::class,
            Features\SupportRedirects\SupportRedirects::class,
            Features\SupportTransitions\SupportTransitions::class,
            Features\SupportStreaming\SupportStreaming::class,
            Features\SupportJsModules\SupportJsModules::class,
            Features\SupportCssModules\SupportCssModules::class,
            Features\SupportNavigate\SupportNavigate::class,
            Features\SupportEntangle\SupportEntangle::class,
            Features\SupportWireRef\SupportWireRef::class,
            Features\SupportRouting\SupportRouting::class,
            Features\SupportLocales\SupportLocales::class,
            Features\SupportTesting\SupportTesting::class,
            Features\SupportIslands\SupportIslands::class,
            Features\SupportModels\SupportModels::class,
            Features\SupportEvents\SupportEvents::class,
            Features\SupportSlots\SupportSlots::class,
            Features\SupportJson\SupportJson::class,

            // Some features we want to have priority over others...
            Features\SupportLifecycleHooks\SupportLifecycleHooks::class,
            Features\SupportLegacyModels\SupportLegacyModels::class,
            Features\SupportWireables\SupportWireables::class,
        ] as $feature) {
            app('livewire')->componentHook($feature);
        }

        ComponentHookRegistry::boot();
    }
}
