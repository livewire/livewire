<?php

namespace Livewire\Features\SupportMultipleRootElementDetection;

use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Component;

class UnitTest extends TestCase
{
    /** @test */
    function two_or_more_root_elements_throws_an_error()
    {
        $this->expectException(MultipleRootElementsDetectedException::class);

        Livewire::test(new class extends Component {
            function render()
            {
                return <<<'HTML'
                <div>
                    First element
                </div>

                <div>
                    Second element
                </div>
                HTML;
            }
        });
    }

    /** @test */
    function allow_script_tags_as_second_element()
    {
        Livewire::test(new class extends Component {
            function render()
            {
                return <<<'HTML'
                <div>
                    First element
                </div>

                <script>
                    let foo = 'bar'
                </script>
                HTML;
            }
        })->assertSuccessful();
    }
}
