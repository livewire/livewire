<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Exceptions\RootTagMissingFromViewException;
use Livewire\Livewire;
use Livewire\LivewireManager;
use function Livewire\str;

class ComponentMorphSelectorTest extends TestCase
{
    /** @test */
    public function component_renders_initial_like_normal()
    {
        $component = Livewire::test(ComponentMorphSelectorStub::class);

        $this->assertTrue(
            str($component->payload['effects']['html'])->contains([$component->id(), 'foo'])
        );
    }

    /** @test */
    public function on_selector_output_contains_selector_and_selector_output()
    {
        $component = Livewire::test(ComponentMorphSelectorStub::class);

        $component->call('click');

        $this->assertSame(
            '<div>Clicked</div>',
            $component->payload['effects']['morphs'][0]['html']
        );
        $this->assertSame('#output', $component->payload['effects']['morphs'][0]['selector']);
    }
}

class ComponentMorphSelectorStub extends Component
{
    public function click()
    {
        $this->morphSelector('#output', 'Clicked');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
