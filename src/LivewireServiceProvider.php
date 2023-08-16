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
        (new \Livewire\EventBus)->boot();
    }

    protected function getMechanisms()
    {
        return [
            \Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware::class,
            \Livewire\Mechanisms\HandleComponents\HandleComponents::class,
            \Livewire\Mechanisms\HandleRequests\HandleRequests::class,
            \Livewire\Mechanisms\FrontendAssets\FrontendAssets::class,
            \Livewire\Mechanisms\ExtendBlade\ExtendBlade::class,
            \Livewire\Mechanisms\CompileLivewireTags::class,
            \Livewire\Mechanisms\ComponentRegistry::class,
            \Livewire\Mechanisms\RenderComponent::class,
            \Livewire\Mechanisms\DataStore::class,
        ];
    }

    protected function registerMechanisms()
    {
        foreach ($this->getMechanisms() as $mechanism) {
            (new $mechanism)->register($this);
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
            (new $mechanism)->boot($this);
        }
    }

    protected function bootFeatures()
    {
        foreach([
            \Livewire\Features\SupportWireModelingNestedComponents\SupportWireModelingNestedComponents::class,
            \Livewire\Features\SupportMultipleRootElementDetection\SupportMultipleRootElementDetection::class,
            \Livewire\Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache::class,
            \Livewire\Features\SupportMorphAwareIfStatement\SupportMorphAwareIfStatement::class,
            \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::class,
            \Livewire\Features\SupportComputed\SupportLegacyComputedPropertySyntax::class,
            \Livewire\Features\SupportNestingComponents\SupportNestingComponents::class,
            \Livewire\Features\SupportBladeAttributes\SupportBladeAttributes::class,
            \Livewire\Features\SupportConsoleCommands\SupportConsoleCommands::class,
            \Livewire\Features\SupportPageComponents\SupportPageComponents::class,
            \Livewire\Features\SupportReactiveProps\SupportReactiveProps::class,
            \Livewire\Features\SupportFileDownloads\SupportFileDownloads::class,
            \Livewire\Features\SupportJsEvaluation\SupportJsEvaluation::class,
            \Livewire\Features\SupportQueryString\SupportQueryString::class,
            \Livewire\Features\SupportFileUploads\SupportFileUploads::class,
            \Livewire\Features\SupportTeleporting\SupportTeleporting::class,
            \Livewire\Features\SupportLazyLoading\SupportLazyLoading::class,
            \Livewire\Features\SupportFormObjects\SupportFormObjects::class,
            \Livewire\Features\SupportAttributes\SupportAttributes::class,
            \Livewire\Features\SupportPagination\SupportPagination::class,
            \Livewire\Features\SupportValidation\SupportValidation::class,
            \Livewire\Features\SupportRedirects\SupportRedirects::class,
            \Livewire\Features\SupportWireables\SupportWireables::class,
            \Livewire\Features\SupportStreaming\SupportStreaming::class,
            \Livewire\Features\SupportNavigate\SupportNavigate::class,
            \Livewire\Features\SupportEntangle\SupportEntangle::class,
            \Livewire\Features\SupportLocales\SupportLocales::class,
            \Livewire\Features\SupportTesting\SupportTesting::class,
            \Livewire\Features\SupportModels\SupportModels::class,
            \Livewire\Features\SupportEvents\SupportEvents::class,

            // Some features we want to have priority over others...
            \Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks::class,
            \Livewire\Features\SupportLegacyModels\SupportLegacyModels::class,
        ] as $feature) {
            app('livewire')->componentHook($feature);
        }

        ComponentHookRegistry::boot();
    }
}


