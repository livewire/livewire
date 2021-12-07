<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class WorksOnLoadBalancersTest extends TestCase
{
    /** @test */
    public function livewire_renders_chidren_properly_across_load_balancers()
    {
        Livewire::component('parent', LoadBalancerParentComponent::class);
        Livewire::component('child', LoadBalancerChildComponent::class);

        $component = Livewire::test('parent');

        $component->assertSee('child-content-1');
        $component->assertSee('child-content-2');

        // Nuke the view cache to simulate two different servers serving the same
        // livewire page for different requests.
        File::cleanDirectory(__DIR__.'/../../vendor/orchestra/testbench-core/laravel/storage/framework/views');

        $component->call('$refresh');

        $component->assertDontSee('child-content-1');
        $component->assertDontSee('child-content-2');
    }
}

class LoadBalancerParentComponent extends Component
{
    public function render()
    {
        return view('load-balancer-parent');
    }
}

class LoadBalancerChildComponent extends Component
{
    public $number;

    public function render()
    {
        return view('load-balancer-child');
    }
}
