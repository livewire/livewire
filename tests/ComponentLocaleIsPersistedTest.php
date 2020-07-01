<?php

namespace Tests;

use Illuminate\Support\Facades\App;
use Livewire\Livewire;
use Livewire\Component;

class ComponentLocaleIsPersisted extends TestCase
{
    /** @test */
    public function a_livewire_component_can_persist_its_locale()
    {
        $component = Livewire::test(ComponentForLocalePersistanceHydrationMiddleware::class)
            ->assertSet('locale', null);

        // Ensure locale isn't persisted in the test session
        App::setLocale('en');

        $component->call('checkLocale')
            ->assertSet('locale', 'es');
    }
}

class ComponentForLocalePersistanceHydrationMiddleware extends Component
{
    public $locale;

    public function checkLocale()
    {
        $this->locale = App::getLocale();
    }

    public function mount()
    {
        App::setLocale('es');
    }

    public function render()
    {
        return view('null-view');
    }
}
