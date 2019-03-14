<?php

use Livewire\Livewire;
use Livewire\LivewireComponent;
use PHPUnit\Framework\TestCase;
use Livewire\LivewireManager;
use Livewire\LivewireComponentWrapper;

class ComponentCanReceiveMultipleActions extends TestCase
{
    /** @test */
    function can_sync_input_data()
    {
        $instance = LivewireComponentWrapper::wrap(new FaucetStub('id', 'wire'));
        $instance->syncInput('modelNumber', '123abc');
        $this->assertequals('123abc', $instance->modelNumber);
    }

    /** @test */
    function synced_data_shows_up_as_dirty_if_changed_from_something_other_than_sync()
    {
        $component = new FaucetStub('id', $prefix = 'wire');

        $instance = LivewireComponentWrapper::wrap($component);
        $instance->syncInput('modelNumber', '123abc');
        $this->assertEmpty($instance->dirtyInputs());

        // We need to re-wrap the the component to reset the dirtyInput tracking.
        $instance = LivewireComponentWrapper::wrap($component);
        $instance->wrapped->changeModelNumber('456def');
        $this->assertContains('modelNumber', $instance->dirtyInputs());
    }

    /** @test */
    function lazy_synced_data_doesnt_shows_up_as_dirty()
    {
        $component = new FaucetStub('id', $prefix = 'wire');

        $instance = LivewireComponentWrapper::wrap($component);
        $instance->lazySyncInput('modelNumber', '123abc');
        $this->assertEmpty($instance->dirtyInputs());
    }
}

class FaucetStub extends LivewireComponent {
    public $modelNumber;

    public function changeModelNumber($number)
    {
        $this->modelNumber = $number;
    }

    public function render()
    {
        //
    }
}
