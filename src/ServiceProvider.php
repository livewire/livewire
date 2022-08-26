<?php

namespace Livewire;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Hey! Let me tell you a bit about how this codebase
     * works. Let's start with the high level philosophies,
     * then get sepecific.
     *
     * 1) The Single File Principle
     *
     *    When approaching a new feature, or maintaining an existing one,
     *    challenge yourself to contain it inside a single file rather
     *    than spreading its code all around the codebase.
     *
     *    This helps to colocate code concerned with the same job,
     *    which eases the maintenance burden & contains the messes.
     *
     * 2) Ease Of Deletion
     *
     *    Each feauture should ideally be "unpluggable". In other words,
     *    you should be able to remove an entire feauture by removing
     *    a single file or by commenting out a single line.
     *
     *    This makes maintenance and debugging easier by encouraging
     *    you to minimize concrete dependancies and instead rely
     *    on flexible abstractions.
     *
     * ## Specifics:
     *
     * 1) "Mechanisms" & "Features"
     *    Most new development will likely be contained in one of these
     *    two folders. "Mechanisms" are single files concerned with a
     *    single part of Livewire's core.
     *
     *    "Features" defer from "Mechanisms" in that they should be
     *    unpluggable. Meaning other parts of the system don't rely
     *    on them to function.
     *
     * 2) Hooks
     *    Within most Mechanisms or Features, you will find event listeners
     *    like: "->on('...', function(...) {". These are "hooks' and are
     *    crucial to successfully containing your code in single files.
     *
     * 3) Associated Component Data
     *    Sometimes "Mechanisms" or "Features" need to associate data with
     *    specific Livewire components. Rather than storing arbitrary
     *    data on the component objects themselves, you should
     *    instead use the "ComponentDataStore" class.
     *
     * Hoefully the above helped give you some context for the structure
     * of this codebase. We haven't even touched on the entire component
     * lifecycle and it's data structures. This topic is so complex
     * it deserves it's own codebase and has been isolated to "Synthetic".
     *
     * Thanks for reading,
     * - Caleb
     */

    public function register()
    {
        $this->registerLivewireSingleton();
    }

    public function boot()
    {
        $this->registerSynthesizers();
        $this->registerMechanisms();
        $this->registerFeatures();

        if (app()->environment('testing')) {
            DuskTestCase::runOnApplicationBoot();
        };
    }

    protected function registerLivewireSingleton()
    {
        $this->app->alias(Manager::class, 'livewire');
        $this->app->singleton(Manager::class);
    }

    protected function registerSynthesizers()
    {
        app('synthetic')->registerSynth([
            \Livewire\Synthesizers\LivewireSynth::class,
            \Livewire\Synthesizers\EloquentModelSynth::class,
        ]);
    }

    protected function registerMechanisms()
    {
        foreach ([
            \Livewire\Mechanisms\JavaScriptAndCssAssets::class,
            \Livewire\Mechanisms\CompileLivewireTags::class,
            \Livewire\Mechanisms\ComponentDataStore::class,
            \Livewire\Mechanisms\RenderComponent::class,
            \Livewire\Mechanisms\HijackBlade::class,
        ] as $mechanism) {
            if (in_array(\Livewire\Drawer\IsSingleton::class, class_uses($mechanism))) {
                $mechanism::getInstance()->boot();
            } else {
                (new $mechanism)->boot();
            }
        }
    }

    protected function registerFeatures()
    {
        foreach ([
            \Livewire\Features\SupportWireModelingNestedComponents::class,
            \Livewire\Features\SupportChecksumErrorDebugging::class,
            \Livewire\Features\SupportMorphAwareIfStatement::class,
            \Livewire\Features\SupportLockedProperties::class,
            \Livewire\Features\SupportReactiveProps::class,
            \Livewire\Features\SupportSlots::class,
            \Livewire\Features\SupportLazyLoading::class, // This has to be after "SupportSlots"...
        ] as $feature) {
            if (in_array(\Livewire\Drawer\IsSingleton::class, class_uses($feature))) {
                $feature::getInstance()->boot();
            } else {
                (new $feature)->boot();
            }
        }
    }
}
