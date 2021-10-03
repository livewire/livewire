<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Livewire;

class ComponentStateTest extends TestCase
{
    /** @test */
    public function state_contains_redirector()
    {
        $component = Livewire::test(StateComponent::class);
        $component->call('triggerRedirect');

        $instance = $component->instance();

        $this->assertNotNull($instance->getState('redirector', 'original'));
    }

    /** @test */
    public function state_contains_returns()
    {
        $component = Livewire::test(StateComponent::class);
        $component->call('sum', 1, 3);

        $instance = $component->instance();

        $this->assertNotEmpty($instance->getState('action.returns'));
        $returns = $instance->getState('action.returns');
        $id = array_key_first($returns);
        $this->assertEquals(4, $returns[$id]);
    }

    /** @test */
    public function state_contains_html_hash()
    {
        $component = Livewire::test(StateComponent::class);
        $component->call('sum', 1, 3);

        $instance = $component->instance();

        $this->assertNotEmpty($instance->getState('html', 'hash'));
    }

    /** @test */
    public function state_contains_downloads()
    {
        $component = Livewire::test(StateComponent::class);
        $component->call('download');

        $instance = $component->instance();

        $this->assertNotEmpty($instance->getState('file', 'download'));
        $this->assertEquals('download.txt', $instance->getState('file', 'download')['name']);
    }
}

class StateComponent extends Component
{
    public $name;

    protected $queryParams = ['name'];

    public function mount()
    {
        $this->name = 'testing';
    }

    public function triggerRedirect()
    {
        $this->redirect('/undefined-url');
    }

    public function sum($n1, $n2)
    {
        return $n1 + $n2;
    }

    public function download($filename = null, $headers = [])
    {
        return Storage::disk('unit-downloads')->download('download.txt', $filename, $headers);
    }

    public function render()
    {
        return <<<HTML
            <div></div>
        HTML;
    }
}
