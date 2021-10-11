<?php

namespace Tests\Browser\Stacks;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test_conditionally_loaded_component_can_push_and_preppend_to_stack()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->assertPresent('@parent-stack-push')
                ->assertPresent('@parent-stack-prepend')
                ->assertNotPresent('@child-stack-push')
                ->assertNotPresent('@child-stack-prepend')
                ->waitForLivewire()->click('@show-child')
                ->assertPresent('@parent-stack-push')
                ->assertPresent('@parent-stack-prepend')
                ->assertPresent('@child-stack-push')
                ->assertPresent('@child-stack-prepend')
            ;
        });
    }
}
