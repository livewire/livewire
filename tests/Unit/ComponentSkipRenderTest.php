<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use function Livewire\str;

class ComponentSkipRenderTest extends TestCase
{
    /** @test */
    public function component_renders_like_normal()
    {
        $component = Livewire::test(ComponentSkipRenderStub::class);

        $this->assertTrue(
            str($component->payload['effects']['html'])->contains([$component->id(), 'foo'])
        );
    }

    /** @test */
    public function on_skip_render_render_is_not_called()
    {
        $component = Livewire::test(ComponentSkipRenderStub::class);

        $component->call('noop');

        $this->assertNull($component->payload['effects']['html']);
    }
}

class ComponentSkipRenderStub extends Component
{
    private $noop = false;

    public function noop()
    {
        $this->noop = true;

        $this->skipRender();
    }

    public function render()
    {
        if ($this->noop) {
            throw new \RuntimeException('Render should not be called after noop()');
        }

        return app('view')->make('null-view');
    }
}
