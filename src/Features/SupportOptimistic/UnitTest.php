<?php

namespace Livewire\Features\SupportOptimistic;

use Livewire\Attributes\Optimistic;
use Livewire\Livewire;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_optimistic_attribute_adds_method_to_snapshot_memo()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[Optimistic]
            public function save()
            {
                //
            }
        });

        $this->assertContains('save', data_get($component->snapshot, 'memo.optimistic', []));
    }
}
