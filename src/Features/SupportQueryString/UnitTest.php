<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Component;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function can_track_properties_in_the_url()
    {
        $component = Livewire::test(new class extends Component
        {
            #[BaseUrl]
            public $count = 1;

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return '<div></div>';
            }
        });

        $this->assertTrue(isset($component->effects['url']));
    }
}
