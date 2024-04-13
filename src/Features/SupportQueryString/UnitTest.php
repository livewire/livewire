<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Livewire;
use Livewire\Component;
use PHPUnit\Framework\Attributes\Test;

class UnitTest extends \Tests\TestCase
{
    #[Test]
    function can_track_properties_in_the_url()
    {
        $component = Livewire::test(new class extends Component {
            #[BaseUrl]
            public $count = 1;

            function increment() { $this->count++; }

            public function render() {
                return '<div></div>';
            }
        });

        $this->assertTrue(isset($component->effects['url']));
    }
}
