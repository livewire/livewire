<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use Illuminate\Routing\UrlGenerator;

class ComponentDependencyInjectionTest extends TestCase
{
    /** @test */
    public function component_mount_action_with_dependency()
    {
        $component = app(LivewireManager::class)->test(ComponentWithDependencyInjection::class, ['id' => 123]);

        $this->assertEquals('http://localhost/some-url/123', $component->foo);
        $this->assertEquals(123, $component->bar);
    }

    /** @test */
    public function component_action_with_dependency()
    {
        $component = app(LivewireManager::class)->test(ComponentWithDependencyInjection::class);

        $component->runAction('injection', 'foobar');

        $this->assertEquals('http://localhost', $component->foo);
        $this->assertEquals('foobar', $component->bar);
    }

    /** @test */
    public function component_action_with_primitive()
    {
        $component = app(LivewireManager::class)->test(ComponentWithDependencyInjection::class);

        $component->runAction('primitive', 1);

        $this->assertEquals(1, $component->foo);
        $this->assertEquals('', $component->bar);
    }

    /** @test */
    public function component_action_with_dependency_and_primitive()
    {
        $component = app(LivewireManager::class)->test(ComponentWithDependencyInjection::class);

        $component->runAction('mixed', 1);

        $this->assertEquals('http://localhost/some-url/1', $component->foo);
        $this->assertEquals(1, $component->bar);
    }
}

class ComponentWithDependencyInjection extends Component
{
    public $foo;
    public $bar;

    public function mount(UrlGenerator $generator, $id = 123)
    {
        $this->foo = $generator->to('/some-url', $id);
        $this->bar = $id;
    }

    public function injection(UrlGenerator $generator, $bar)
    {
        $this->foo = $generator->to('/');
        $this->bar = $bar;
    }

    public function primitive(int $foo, $bar = '')
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function mixed(UrlGenerator $generator, int $id)
    {
        $this->foo = $generator->to('/some-url', $id);
        $this->bar = $id;
    }

    public function render()
    {
        return view('null-view');
    }
}
