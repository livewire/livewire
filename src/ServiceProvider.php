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
        $config = __DIR__.'/../config/livewire.php';

        $this->publishes([$config => base_path('config/livewire.php')], ['livewire', 'livewire:config']);

        $this->mergeConfigFrom($config, 'livewire');
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
            (new $mechanism)->boot($this);
        }
    }

    protected function bootFeatures()
    {
        // By providing this feature only as an attribute we lose the ability to scan the service provider to see it,
        // probably not a big deal, but feels a little weird...
        // app('livewire')->componentHook(\Livewire\Features\SupportLockedProperties\SupportLockedProperties::class);
        app('livewire')->componentHook(\Livewire\Features\SupportDirtyDetection\SupportDirtyDetection::class);
        app('livewire')->componentHook(\Livewire\Features\SupportFileDownloads\SupportFileDownloads::class);
        app('livewire')->componentHook(\Livewire\Features\SupportQueryString\SupportQueryString::class);
        app('livewire')->componentHook(\Livewire\Features\SupportRedirects\SupportRedirects::class);
        app('livewire')->componentHook(\Livewire\Features\SupportLocales\SupportLocales::class);
        app('livewire')->componentHook(\Livewire\Features\SupportEvents\SupportEvents::class);

        // Refactor this...
        $hooks = app(\Livewire\Mechanisms\ComponentRegistry::class)->getComponentHooks();
        (new HookAdapter)->adapt($hooks);

        foreach ([
            // V3
            \Livewire\Features\SupportWireModelingNestedComponents\SupportWireModelingNestedComponents::class,
            \Livewire\Features\SupportChecksumErrorDebugging\SupportChecksumErrorDebugging::class,
            \Livewire\Features\SupportMorphAwareIfStatement\SupportMorphAwareIfStatement::class,
            \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::class,
            \Livewire\Features\SupportPersistedLayouts\SupportPersistedLayouts::class,
            \Livewire\Features\SupportReactiveProps\SupportReactiveProps::class,
            \Livewire\Features\SupportHotReloading\SupportHotReloading::class,
            \Livewire\Features\SupportLazyLoading\SupportLazyLoading::class,
            \Livewire\Features\SupportTeleporting\SupportTeleporting::class,

            // Core
            \Livewire\Features\SupportJavaScriptOrderedArrays\SupportJavaScriptOrderedArrays::class,
            \Livewire\Features\SupportComputedProperties\SupportComputedProperties::class,
            \Livewire\Features\SupportNestingComponents\SupportNestingComponents::class,
            \Livewire\Features\SupportBladeAttributes\SupportBladeAttributes::class,
            \Livewire\Features\SupportConsoleCommands\SupportConsoleCommands::class,
            \Livewire\Features\SupportPageComponents\SupportPageComponents::class,
            \Livewire\Features\SupportUnitTesting\SupportUnitTesting::class,
            \Livewire\Features\SupportFileUploads\SupportFileUploads::class,
            \Livewire\Features\SupportPagination\SupportPagination::class,
            \Livewire\Features\SupportValidation\SupportValidation::class,
            \Livewire\Features\SupportEntangle\SupportEntangle::class,
            \Livewire\Features\SupportModels\SupportModels::class,
            \Livewire\Features\SupportTraits\SupportTraits::class,

            // V2 parity
            \Livewire\Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache::class,
            \Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks::class,
            \Livewire\Features\SupportWireables\SupportWireables::class,

        ] as $feature) {
            (new $feature)->boot($this);
        }
    }
}


