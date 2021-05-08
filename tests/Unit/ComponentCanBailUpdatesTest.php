<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;

class ComponentCanBailUpdatesTest extends TestCase
{
    /** @test */
    public function it_bails_in_the_updating_hook()
    {
        Livewire::test(ComponentWithUpdatingHookThatBails::class)
            ->assertSet('name', 'Jeff')
            ->set('name', 'Jiff')
            ->assertSet('name', 'Jeff')
            ->assertSet('age', 77)
            ->set('age', 78)
            ->assertSet('age', 77)
            ->assertSet('traits.interests', ['Breathing', 'Sticks'])
            ->set('traits.interests', ['Breathing'])
            ->assertSet('traits.interests', ['Breathing', 'Sticks']);
    }

    /** @test */
    public function it_bails_in_the_trait_updating_hook()
    {
        Livewire::test(ComponentWithTraitUpdatingHookThatBails::class)
            ->assertSet('name', 'Jeff')
            ->set('name', 'Jiff')
            ->assertSet('name', 'Jeff');
    }
}

class ComponentWithUpdatingHookThatBails extends Component
{
    public $name = 'Jeff';
    public $age = 77;
    public $traits = [
        'interests' => ['Breathing', 'Sticks'],
    ];

    public function updating($attribute)
    {
        if ($attribute === 'name') {
            return false;
        }
    }

    public function updatingTraitsInterests()
    {
        return false;
    }

    public function updatingAge()
    {
        return false;
    }

    public function render()
    {
        return view('null-view');
    }
}

trait TraitWithUpdatingHook
{
    public function updatingTraitWithUpdatingHook()
    {
        return false;
    }
}

class ComponentWithTraitUpdatingHookThatBails extends Component
{
    use TraitWithUpdatingHook;

    public $name = 'Jeff';

    public function render()
    {
        return view('null-view');
    }
}
