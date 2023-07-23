<?php

namespace Livewire\Tests;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class ComponentCanListenForEventsFromChildrenTest extends TestCase
{
    /** @test */
    public function can_receive_events_from_children()
    {
        Livewire::test(ExampleComponent::class)
            ->call('exampleMethod')
            ->assertSet('exampleProperty', 'Property set!');
    }
}

class ExampleComponent extends Component
{
    public string $exampleProperty;

    #[On('example-event', fromChildren: true)]
    public function exampleMethod(): void
    {
        $this->exampleProperty = 'Property set!';
    }

    public function render()
    {
        return view('null-view');
    }
}
