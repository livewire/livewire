<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentSessionTest extends TestCase
{
    /** @test */
    function component_can_store_things_in_session()
    {
        $component = app(LivewireManager::class)->test(ComponentWithSession::class);

        $component
            ->assertSet('foo', null)
            ->call('setValueFromSession')
            ->assertSet('foo', 'bar');
    }

    /** @test */
    function protected_properties_are_dehydrated_into_the_session_and_not_in_the_payload()
    {
        $component = app(LivewireManager::class)->test(ComponentWithSession::class);

        $component->assertSet('fiz', 'buz');
    }

    /** @test */
    function old_component_data_stored_in_session_is_destroyed_when_a_new_page_is_loaded_within_the_same_window()
    {
        $this->markTestSkipped();
        // $component = app(LivewireManager::class)->test(ComponentWithSession::class);

        // $sessionKey = $component->id.'protected_properties';

        // $this->assertEquals('hey', session()->all());
    }
}

class ComponentWithSession extends Component {
    public $foo;
    protected $fiz;

    public function mount()
    {
        $this->session('foo', 'bar');
        $this->fiz = 'buz';
    }

    public function setValueFromSession()
    {
        $this->foo = $this->session('foo');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
