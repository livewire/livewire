<?php

namespace Livewire\Features\SupportDirty;

use Tests\TestComponent;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    function test_rebaseline_adds_an_effect_for_the_request_that_called_it()
    {
        $component = Livewire::test(new class extends TestComponent
        {
            public $title = '';

            public function save()
            {
                $this->rebaseline();
            }

            public function touch() {}
        });

        $this->assertArrayNotHasKey('rebaseline', $component->effects);

        $component->call('save');

        $this->assertTrue($component->effects['rebaseline']);

        $component->call('touch');

        $this->assertArrayNotHasKey('rebaseline', $component->effects);
    }
}
