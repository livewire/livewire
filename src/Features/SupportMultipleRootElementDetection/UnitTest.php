<?php

namespace Livewire\Features\SupportMultipleRootElementDetection;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Component;

class UnitTest extends TestCase
{
    #[Test]
    function two_or_more_root_elements_throws_an_error()
    {
        config()->set('app.debug', true);

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

    #[Test]
    function allow_script_tags_as_second_element()
    {
        config()->set('app.debug', true);

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

    #[Test]
    function dont_throw_error_in_production_so_that_there_is_no_perf_penalty()
    {
        config()->set('app.debug', false);

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
