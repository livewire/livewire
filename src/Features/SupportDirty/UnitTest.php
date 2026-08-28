<?php

namespace Livewire\Features\SupportDirty;

use Tests\TestComponent;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    function test_mark_as_clean_adds_an_effect_to_the_current_message()
    {
        $component = Livewire::test(new class extends TestComponent {
            public function save()
            {
                $this->markAsClean();
            }

            public function touch() {}
        });

        $this->assertArrayNotHasKey('markClean', $component->effects);

        $component->call('save');

        $this->assertTrue($component->effects['markClean']);

        $component->call('touch');

        $this->assertArrayNotHasKey('markClean', $component->effects);
    }
}
