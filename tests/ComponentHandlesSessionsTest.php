<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentHandlesSessionsTest extends TestCase
{
    /** @test */
    public function removes_flashed_message_without_redirect()
    {
        $component = app(LivewireManager::class)->test(RemovesFlashedMessageWithoutRedirect::class);

        $component->call('flashMessage');
        $component->assertSee('Task was successfull!');
        $component->set('foo', 'bar'); // Modify some property to force a refresh.
        $component->assertDontSee('Task was successfull!');
    }

    /** @test */
    public function keeps_flashed_message_with_redirect()
    {
        $component = app(LivewireManager::class)->test(KeepsFlashedMessageWithRedirect::class);

        $component->call('flashMessage');
        $component->assertSee('Task was successfull!');
        $component->set('foo', 'bar'); // Modify some property to force a refresh.
        $component->assertSee('Task was successfull!');
    }
}

class RemovesFlashedMessageWithoutRedirect extends Component
{
    public $foo;

    public function render()
    {
        return app('view')->make('show-session-flash');
    }

    public function flashMessage()
    {
        session()->flash('status', 'Task was successfull!');
    }
}

class KeepsFlashedMessageWithRedirect extends Component
{
    public $foo;

    public function render()
    {
        return app('view')->make('show-session-flash');
    }

    public function flashMessage()
    {
        session()->flash('status', 'Task was successfull!');

        $this->redirect('/foo');
    }
}
