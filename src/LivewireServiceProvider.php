<?php

namespace Livewire;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;

class LivewireServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->registerLivewireSingleton();
        $this->registerConfig();
        $this->bootEventBus();
        $this->registerMechanisms();
    }

    public function boot()
    {
        $this->bootMechanisms();
        $this->bootFeatures();
    }

    protected function registerLivewireSingleton()
    {
        $this->app->alias(LivewireManager::class, 'livewire');

        $this->app->singleton(LivewireManager::class);

        app('livewire')->setProvider($this);
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
            Mechanisms\ComponentRegistry::class,
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
            Features\SupportQueryString\SupportQueryString::class,
            Features\SupportFileUploads\SupportFileUploads::class,
            Features\SupportTeleporting\SupportTeleporting::class,
            Features\SupportLazyLoading\SupportLazyLoading::class,
            Features\SupportFormObjects\SupportFormObjects::class,
            Features\SupportAttributes\SupportAttributes::class,
            Features\SupportPagination\SupportPagination::class,
            Features\SupportValidation\SupportValidation::class,
            Features\SupportIsolating\SupportIsolating::class,
            Features\SupportRedirects\SupportRedirects::class,
            Features\SupportStreaming\SupportStreaming::class,
            Features\SupportNavigate\SupportNavigate::class,
            Features\SupportEntangle\SupportEntangle::class,
            Features\SupportLocales\SupportLocales::class,
            Features\SupportTesting\SupportTesting::class,
            Features\SupportModels\SupportModels::class,
            Features\SupportEvents\SupportEvents::class,

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
