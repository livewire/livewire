<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class LivewireTestingTest extends TestCase
{
    /** @test */
    function test_method_accepts_arguments_to_pass_to_mount()
    {
        $component = app(LivewireManager::class)
            ->test(HasMountArguments::class, 'foo');

        $this->assertContains('foo', $component->dom);
    }
}

class HasMountArguments extends Component
{
    public $name;

    public function mount($name)
    {
        $this->name = $name;
    }

    public function render()
    {
        return app('view')->make('show-name');
    }
}
