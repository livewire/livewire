<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentSessionTest extends TestCase
{
    /** @test */
    public function component_can_store_things_in_session()
    {
        $component = app(LivewireManager::class)->test(ComponentWithSession::class);

        $component
            ->assertSet('foo', null)
            ->call('setValueFromSession')
            ->assertSet('foo', 'bar');
    }

    /** @test */
    public function protected_properties_are_dehydrated_into_the_session_and_not_in_the_payload()
    {
        $component = app(LivewireManager::class)->test(ComponentWithSession::class);

        $component->call('setValueOfFiz', 'bluth');

        $this->assertNotContains('fiz', $component->data);
        $this->assertEquals('bluth', session()->get("{$component->id}:protected_properties")['fiz']);
    }

    /** @test */
    public function protected_properties_are_rehydrated_from_the_session()
    {
        $component = app(LivewireManager::class)->test(ComponentWithSession::class);

        $this->assertNotEquals('bluth', $component->fiz);

        session()->put("{$component->id}:protected_properties", ['fiz' => 'bluth']);

        $component->call('$refresh');

        $this->assertEquals('bluth', $component->fiz);
    }
}

class ComponentWithSession extends Component
{
    public $foo;
    protected $fiz;

    public function mount()
    {
        $this->session('foo', 'bar');

        $this->fiz = 'buz';
    }

    public function setValueOfFiz($value)
    {
        $this->fiz = $value;
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
