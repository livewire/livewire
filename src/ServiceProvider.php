<?php

namespace Livewire;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->registerLivewireSingleton();
        $this->registerConfig();
    }

    public function boot()
    {
        $this->bootEventBus();
        $this->bootMechanisms();
        $this->bootFeatures();
    }

    protected function registerLivewireSingleton()
    {
        $this->app->alias(Manager::class, 'livewire');
        $this->app->singleton(Manager::class);
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/livewire.php', 'livewire');
    }

    protected function bootEventBus()
    {
        (new \Livewire\EventBus)->boot();
    }

    protected function bootMechanisms()
    {
        foreach ([
            \Livewire\Mechanisms\UpdateComponents\UpdateComponents::class,
            \Livewire\Mechanisms\ExtendBlade\ExtendBlade::class,
            \Livewire\Mechanisms\CompileLivewireTags::class,
            \Livewire\Mechanisms\ComponentRegistry::class,
            \Livewire\Mechanisms\RenderComponent::class,
            \Livewire\Mechanisms\FrontendAssets::class,
            \Livewire\Mechanisms\DataStore::class,
        ] as $mechanism) {
            (new $mechanism)->boot();
        }
    }

    protected function bootFeatures()
    {
        foreach ([
            \Livewire\Features\SupportWireModelingNestedComponents\SupportWireModelingNestedComponents::class,
            \Livewire\Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache::class,
            \Livewire\Features\SupportJavaScriptOrderedArrays\SupportJavaScriptOrderedArrays::class,
            \Livewire\Features\SupportChecksumErrorDebugging\SupportChecksumErrorDebugging::class,
            \Livewire\Features\SupportMorphAwareIfStatement\SupportMorphAwareIfStatement::class,
            \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::class,
            \Livewire\Features\SupportComputedProperties\SupportComputedProperties::class,
            \Livewire\Features\SupportLivewireDirective\SupportLivewireDirective::class,
            \Livewire\Features\SupportNestingComponents\SupportNestingComponents::class,
            \Livewire\Features\SupportLockedProperties\SupportLockedProperties::class,
            \Livewire\Features\SupportPersistedLayouts\SupportPersistedLayouts::class,
            \Livewire\Features\SupportBladeAttributes\SupportBladeAttributes::class,
            \Livewire\Features\SupportConsoleCommands\SupportConsoleCommands::class,
            \Livewire\Features\SupportPageComponents\SupportPageComponents::class,
            \Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks::class,
            \Livewire\Features\SupportDirtyDetection\SupportDirtyDetection::class,
            \Livewire\Features\SupportReactiveProps\SupportReactiveProps::class,
            \Livewire\Features\SupportFileDownloads\SupportFileDownloads::class,
            \Livewire\Features\SupportThisDirective\SupportThisDirective::class,
            \Livewire\Features\SupportHotReloading\SupportHotReloading::class,
            \Livewire\Features\SupportLazyLoading\SupportLazyLoading::class,
            \Livewire\Features\SupportTeleporting\SupportTeleporting::class,
            \Livewire\Features\SupportUnitTesting\SupportUnitTesting::class,
            \Livewire\Features\SupportFileUploads\SupportFileUploads::class,
            \Livewire\Features\SupportValidation\SupportValidation::class,
            \Livewire\Features\SupportWireables\SupportWireables::class,
            \Livewire\Features\SupportRedirects\SupportRedirects::class,
            \Livewire\Features\SupportEntangle\SupportEntangle::class,
            \Livewire\Features\SupportLocales\SupportLocales::class,
            \Livewire\Features\SupportModels\SupportModels::class,
            \Livewire\Features\SupportTraits\SupportTraits::class,
            \Livewire\Features\SupportEvents\SupportEvents::class,
        ] as $feature) {
            (new $feature)->boot();
        }
    }
}
