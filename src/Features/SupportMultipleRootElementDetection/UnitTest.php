<?php

namespace Livewire\Features\SupportMultipleRootElementDetection;

use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class UnitTest extends TestCase
{
    /** @test */
    public function two_or_more_root_elements_throws_an_error()
    {
        config()->set('app.debug', true);

        $this->expectException(MultipleRootElementsDetectedException::class);

        Livewire::test(new class extends Component
        {
            public function render()
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
    public function allow_script_tags_as_second_element()
    {
        config()->set('app.debug', true);

        Livewire::test(new class extends Component
        {
            public function render()
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
    public function dont_throw_error_in_production_so_that_there_is_no_perf_penalty()
    {
        config()->set('app.debug', false);

        Livewire::test(new class extends Component
        {
            public function render()
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
