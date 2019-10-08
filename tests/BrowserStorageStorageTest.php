<?php

namespace Tests;

use Livewire\Component;
use Livewire\ProtectedStorage\ProtectedStorageInBrowser;
use Livewire\LivewireManager;
use Livewire\Testing\TestableLivewire;

class BrowserStorageTest extends TestCase
{
    /** @test */
    public function protected_properties_are_dehydrated()
    {
        /** @var TestableLivewire $component */
        $component = app(LivewireManager::class)->test(BrowserStorageTestComponent::class);

        $component->call('setValueOfFiz', 'bluth');

        /** @var BrowserStorageTestComponent $instance */
        $instance = $component->instance;

        $this->assertInstanceOf(ProtectedStorageInBrowser::class, $instance->getProtectedStorageEngine());
        $this->assertEquals('bluth', $component->fiz);
    }

}

class BrowserStorageTestComponent extends Component
{
    public $foo;
    protected $fiz;

    public function getProtectedStorage()
    {
        return 'browser';
    }

    public function mount()
    {
        $this->fiz = 'buz';
    }

    public function setValueOfFiz($value)
    {
        $this->fiz = $value;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }

    public function getFiz()
    {
        return $this->fiz;
    }
}
