<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class MountComponentTest extends TestCase
{
    /** @test */
    public function it_resolves_the_mount_parameters()
    {
        $component = app(LivewireManager::class)->test(ComponentWithOptionalParameters::class);
        $this->assertSame(null, $component->foo);
        $this->assertSame([], $component->bar);

        $component = app(LivewireManager::class)->test(ComponentWithOptionalParameters::class, 'caleb');
        $this->assertSame('caleb', $component->foo);
        $this->assertSame([], $component->bar);

        $component = app(LivewireManager::class)->test(ComponentWithOptionalParameters::class, null, 'porzio');
        $this->assertSame(null, $component->foo);
        $this->assertSame('porzio', $component->bar);

        $component = app(LivewireManager::class)->test(ComponentWithOptionalParameters::class, 'caleb', 'porzio');
        $this->assertSame('caleb', $component->foo);
        $this->assertSame('porzio', $component->bar);

        $component = app(LivewireManager::class)->test(ComponentWithOptionalParameters::class, null, null);
        $this->assertSame(null, $component->foo);
        $this->assertSame(null, $component->bar);
    }
}

class ComponentWithOptionalParameters extends Component
{
    public $foo;
    public $bar;

    public function mount($foo = null, $bar = [])
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function render()
    {
        return view('null-view');
    }
}
