<?php

namespace Livewire\Features\SupportMultipleRootElementDetection;

use Orchestra\Testbench\Attributes\WithConfig;
use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Component;

class UnitTest extends TestCase
{
    /** @test */
    #[WithConfig('app.debug', true)]
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
    #[WithConfig('app.debug', true)]
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

    /** @test */
    #[WithConfig('app.debug', false)]
    function dont_throw_error_in_production_so_that_there_is_no_perf_penalty()
    {
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
        })->assertSuccessful();
    }
}
