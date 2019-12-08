<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentCanResetToInitialData extends TestCase
{
    /** @test */
    public function can_reset_public_properties_to_their_initial_values()
    {
        $component = app(LivewireManager::class)->test(ComponentWithInitialData::class);

        $component->set('firstName', 'initial');
        $component->assertSet('firstName', 'initial');
        $component->set('firstName', 'Caleb');
        $component->assertSet('firstName', 'Caleb');
        $component->resetProperties();
        $component->assertSet('firstName', 'initial');
    }
}

class ComponentWithInitialData extends Component
{
    public $firstName = 'initial';

    public function resetProperties()
    {
        $this->resetPublicProperties();
    }

    public function render()
    {
        return view('null-view');
    }
}
