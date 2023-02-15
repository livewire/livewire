<?php

namespace Livewire;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        app()->bind('livewire.provider', fn () => $this);

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
        app('livewire')->componentHook(\Livewire\Features\SupportDisablingBackButtonCache\SupportDisablingBackButtonCache::class);
        app('livewire')->componentHook(\Livewire\Features\SupportComputedProperties\SupportComputedProperties::class);
        app('livewire')->componentHook(\Livewire\Features\SupportNestingComponents\SupportNestingComponents::class);
        app('livewire')->componentHook(\Livewire\Features\SupportBladeAttributes\SupportBladeAttributes::class);
        app('livewire')->componentHook(\Livewire\Features\SupportConsoleCommands\SupportConsoleCommands::class);
        app('livewire')->componentHook(\Livewire\Features\SupportDirtyDetection\SupportDirtyDetection::class);
        app('livewire')->componentHook(\Livewire\Features\SupportPageComponents\SupportPageComponents::class);
        app('livewire')->componentHook(\Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks::class);
        app('livewire')->componentHook(\Livewire\Features\SupportReactiveProps\SupportReactiveProps::class);
        app('livewire')->componentHook(\Livewire\Features\SupportFileDownloads\SupportFileDownloads::class);
        app('livewire')->componentHook(\Livewire\Features\SupportQueryString\SupportQueryString::class);
        app('livewire')->componentHook(\Livewire\Features\SupportUnitTesting\SupportUnitTesting::class);
        app('livewire')->componentHook(\Livewire\Features\SupportFileUploads\SupportFileUploads::class);
        app('livewire')->componentHook(\Livewire\Features\SupportPagination\SupportPagination::class);
        app('livewire')->componentHook(\Livewire\Features\SupportValidation\SupportValidation::class);
        app('livewire')->componentHook(\Livewire\Features\SupportRedirects\SupportRedirects::class);
        app('livewire')->componentHook(\Livewire\Features\SupportWireables\SupportWireables::class);
        app('livewire')->componentHook(\Livewire\Features\SupportEntangle\SupportEntangle::class);
        app('livewire')->componentHook(\Livewire\Features\SupportLocales\SupportLocales::class);
        app('livewire')->componentHook(\Livewire\Features\SupportEvents\SupportEvents::class);

        // Refactor this...
        $hooks = app(\Livewire\Mechanisms\ComponentRegistry::class)->getComponentHooks();
        (new HookAdapter)->adapt($hooks);

        foreach ([
            // V3
            // \Livewire\Features\SupportWireModelingNestedComponents\SupportWireModelingNestedComponents::class,
            // \Livewire\Features\SupportChecksumErrorDebugging\SupportChecksumErrorDebugging::class,
            // \Livewire\Features\SupportMorphAwareIfStatement\SupportMorphAwareIfStatement::class,
            \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::class,
            // \Livewire\Features\SupportPersistedLayouts\SupportPersistedLayouts::class,
            // \Livewire\Features\SupportHotReloading\SupportHotReloading::class,
            // \Livewire\Features\SupportLazyLoading\SupportLazyLoading::class,
            // \Livewire\Features\SupportTeleporting\SupportTeleporting::class,

            // Core
            // \Livewire\Features\SupportJavaScriptOrderedArrays\SupportJavaScriptOrderedArrays::class, @todo: there might be a better way than this...

        ] as $feature) {
            (new $feature)->boot($this);
        }
    }
}


