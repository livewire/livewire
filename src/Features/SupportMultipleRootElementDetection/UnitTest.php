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

    function test_parsing_html_does_not_trigger_warnings()
    {
        config()->set('app.debug', true);

        // HTML that triggers libxml errors (e.g. block elements nested inside
        // inline elements, or SVG tags on older libxml2 versions)...
        $html = '<div><p><div>nested</div></p></div>';

        // Convert warnings to exceptions so the test fails if any are emitted...
        set_error_handler(function ($severity, $message) {
            throw new \ErrorException($message, 0, $severity);
        }, E_WARNING);

        try {
            (new SupportMultipleRootElementDetection)->getRootElementCount($html);
        } finally {
            restore_error_handler();
        }

        $this->assertTrue(true);
    }

    function test_allow_script_tag_as_only_element()
    {
        config()->set('app.debug', true);

        // Once the <script> tag is stripped there is no markup left to parse...
        Livewire::test(new class extends Component {
            function render()
            {
                return <<<'HTML'
                <script>
                    let foo = 'bar'
                </script>
                HTML;
            }
        })->assertSuccessful();
    }

    function test_allow_style_tag_as_only_element()
    {
        config()->set('app.debug', true);

        Livewire::test(new class extends Component {
            function render()
            {
                return <<<'HTML'
                <style>
                    .foo { color: red; }
                </style>
                HTML;
            }
        })->assertSuccessful();
    }

    function test_allow_entirely_commented_out_template()
    {
        config()->set('app.debug', true);

        // A document containing only a comment is parsed without a <body>...
        Livewire::test(new class extends Component {
            function render()
            {
                return <<<'HTML'
                <!--
                <div>
                    First element
                </div>
                -->
                HTML;
            }
        })->assertSuccessful();
    }

    function test_root_elements_are_counted()
    {
        $detector = new SupportMultipleRootElementDetection;

        $this->assertSame(1, $detector->getRootElementCount('<div>First element</div>'));
        $this->assertSame(2, $detector->getRootElementCount('<div>First element</div><div>Second element</div>'));
        $this->assertSame(1, $detector->getRootElementCount('<div>First element</div><script>let foo = "bar"</script>'));

        // Markup that leaves no elements behind to count...
        $this->assertSame(0, $detector->getRootElementCount('<script>let foo = "bar"</script>'));
        $this->assertSame(0, $detector->getRootElementCount('<style>.foo { color: red; }</style>'));
        $this->assertSame(0, $detector->getRootElementCount("<!--\n<div>First element</div>\n-->"));
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
