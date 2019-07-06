<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class RedirectTest extends TestCase
{
    /** @test */
    function validate_component_properties()
    {
        $component = app(LivewireManager::class)->test(TriggersRedirectStub::class);

        $component->runAction('triggerRedirect');

        $this->assertEquals('/', $component->redirectTo);
    }
}

class TriggersRedirectStub extends Component
{
    public function triggerRedirect()
    {
        $this->redirect('/');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
