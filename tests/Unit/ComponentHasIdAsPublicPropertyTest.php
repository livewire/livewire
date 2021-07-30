<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Exceptions\CannotUseReservedLivewireComponentProperties;
use Livewire\Livewire;

class ComponentHasIdAsPublicPropertyTest extends TestCase
{
    /** @test */
    public function public_id_property_is_set()
    {
        $component = Livewire::test(ComponentWithIdProperty::class);

        $this->assertNotNull($component->id());
    }

    /** @test */
    public function livewires_id_property_cannot_be_overridden_on_child_component()
    {
        $this->expectException(CannotUseReservedLivewireComponentProperties::class);

        $component = Livewire::test(ComponentWithReservedProperties::class);

        $this->assertNotNull($component->id());
    }
}

class ComponentWithIdProperty extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ComponentWithReservedProperties extends Component
{
    public $id = 'foo';

    public function render()
    {
        return app('view')->make('null-view');
    }
}
