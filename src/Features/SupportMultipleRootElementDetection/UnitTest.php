<?php

namespace Livewire\Features\SupportMultipleRootElementDetection;

use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Component;

class UnitTest extends TestCase
{
    public function setUp(): void
    {
        \Livewire\LivewireManager::$v4 = false;

        parent::setUp();
    }

    function test_two_or_more_root_elements_throws_an_error()
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

    function test_allow_script_tags_as_second_element()
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

    function test_allow_script_tags_inside_root_element()
    {
        config()->set('app.debug', true);

        Livewire::test(new class extends Component {
            function render()
            {
                return <<<'HTML'
                <div>
                    <h1>Hello</h1>

                    <script>
                        alert('test')
                    </script>
                </div>
                HTML;
            }
        })->assertSuccessful();
    }

    function test_allow_style_tags_as_second_element()
    {
        config()->set('app.debug', true);

        Livewire::test(new class extends Component {
            function render()
            {
                return <<<'HTML'
                <div>
                    First element
                </div>

                <style>
                    .foo { color: red; }
                </style>
                HTML;
            }
        })->assertSuccessful();
    }

    function test_two_root_elements_with_script_sibling_still_throws_error()
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

                <script>
                    let foo = 'bar'
                </script>

                <div>
                    Second element
                </div>
                HTML;
            }
        });
    }

    function test_two_root_elements_with_style_sibling_still_throws_error()
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

                <style>
                    .foo { color: red; }
                </style>

                <div>
                    Second element
                </div>
                HTML;
            }
        });
    }

    function test_dont_throw_error_in_production_so_that_there_is_no_perf_penalty()
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
