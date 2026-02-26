<?php

namespace Livewire\Features\SupportOfflineQueue;

use Livewire\Attributes\QueueOffline;
use Livewire\Livewire;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_queue_offline_attribute_adds_method_to_snapshot_memo()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[QueueOffline]
            public function save()
            {
                //
            }
        });

        $this->assertContains('save', data_get($component->snapshot, 'memo.offlineQueue', []));
    }
}
