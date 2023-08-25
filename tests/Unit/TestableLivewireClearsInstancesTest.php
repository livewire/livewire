<?php

use Livewire\Component as BaseComponent;
use Livewire\Livewire;
use Livewire\Testing\TestableLivewire;
use Tests\Unit\TestCase;

class TestableLivewireClearsInstancesTest extends TestCase
{
    /** @test */
    public function previous_test()
    {
        Livewire::test(Component::class)->assertSuccessful();
    }

    /** @test */
    public function it_clears_instances()
    {
        $this->assertCount(0, (new ReflectionClass(TestableLivewire::class))->getStaticPropertyValue('instancesById'));
    }
}

class Component extends BaseComponent
{
    protected $shouldSkipRender = true;
}
