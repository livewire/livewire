<?php

namespace Livewire\Features\SupportTransportFragments;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class UnitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('livewire.update_engine', 'delta');
    }

    public function test_it_preserves_laravels_native_blade_fragment_capture()
    {
        $compiled = Blade::compileString(<<<'BLADE'
            @fragment('native-panel')
                <span>Native fragment content</span>
            @endfragment
        BLADE);
        $html = Blade::render(<<<'BLADE'
            @fragment('native-panel')
                <span>Native fragment content</span>
            @endfragment
        BLADE);

        $this->assertStringContainsString("\$__livewireTransportFragmentName = 'native-panel'", $compiled);
        $this->assertStringContainsString(
            '$__env->startFragment($__livewireTransportFragmentName)',
            $compiled,
        );
        $this->assertStringContainsString('$__env->stopFragment()', $compiled);
        $this->assertStringContainsString('<span>Native fragment content</span>', $html);
        $this->assertStringNotContainsString('type=transport', $html);
    }

    public function test_native_fragments_do_not_add_transport_markers_for_the_default_engine()
    {
        config()->set('livewire.update_engine', 'morph');

        $html = Livewire::test(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    @fragment('summary')
                        <span>Native fragment content</span>
                    @endfragment
                </div>
                HTML;
            }
        })->html();

        $this->assertStringContainsString('<span>Native fragment content</span>', $html);
        $this->assertStringNotContainsString('type=transport', $html);
    }

    public function test_fragment_markers_and_tokens_are_stable_across_renders()
    {
        $component = Livewire::test(new class extends Component {
            public int $count = 0;

            public function increment(): void
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    @fragment('summary')
                        <span>{{ $count }}</span>
                    @endfragment
                </div>
                HTML;
            }
        });

        $start = $this->marker('FRAGMENT', 'summary');
        $end = $this->marker('ENDFRAGMENT', 'summary');

        $this->assertStringContainsString($start, $component->html());
        $this->assertStringContainsString($end, $component->html());

        $component->call('increment');

        $this->assertStringContainsString($start, $component->html());
        $this->assertStringContainsString($end, $component->html());
        $this->assertStringContainsString('<span>1</span>', $component->html());
    }

    public function test_dynamic_fragment_names_are_reflected_in_the_markers()
    {
        $component = Livewire::test(new class extends Component {
            public string $fragmentName = 'panel-overview';

            public function render()
            {
                return <<<'HTML'
                <div>
                    @fragment($fragmentName)
                        <span>{{ $fragmentName }}</span>
                    @endfragment
                </div>
                HTML;
            }
        });

        $this->assertStringContainsString($this->marker('FRAGMENT', 'panel-overview'), $component->html());

        $component->set('fragmentName', 'panel-activity');

        $this->assertStringContainsString($this->marker('FRAGMENT', 'panel-activity'), $component->html());
        $this->assertStringNotContainsString($this->marker('FRAGMENT', 'panel-overview'), $component->html());
    }

    public function test_nested_fragments_emit_properly_nested_matching_markers()
    {
        $html = Livewire::test(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    @fragment('dashboard')
                        <section>
                            @fragment('dashboard-table')
                                <table><tr><td>Row</td></tr></table>
                            @endfragment
                        </section>
                    @endfragment
                </div>
                HTML;
            }
        })->html();

        $outerStart = strpos($html, $this->marker('FRAGMENT', 'dashboard'));
        $innerStart = strpos($html, $this->marker('FRAGMENT', 'dashboard-table'));
        $innerEnd = strpos($html, $this->marker('ENDFRAGMENT', 'dashboard-table'));
        $outerEnd = strpos($html, $this->marker('ENDFRAGMENT', 'dashboard'));

        $this->assertIsInt($outerStart);
        $this->assertIsInt($innerStart);
        $this->assertIsInt($innerEnd);
        $this->assertIsInt($outerEnd);
        $this->assertTrue($outerStart < $innerStart);
        $this->assertTrue($innerStart < $innerEnd);
        $this->assertTrue($innerEnd < $outerEnd);
    }

    public function test_end_fragment_without_an_open_fragment_is_rejected()
    {
        $this->expectException(ViewException::class);

        Livewire::test(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    @endfragment
                </div>
                HTML;
            }
        });
    }

    public function test_unclosed_fragment_stack_is_rejected_after_rendering()
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('Unclosed transport fragment stack: [dashboard, dashboard-table].');

        Livewire::test(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    @fragment('dashboard')
                        @fragment('dashboard-table')
                            <span>Rows</span>
                </div>
                HTML;
            }
        });
    }

    public function test_native_fragment_names_that_are_unsafe_for_markers_fall_back_without_changing_output()
    {
        $html = Livewire::test(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    @fragment('unsafe|name')
                        <span>Rows</span>
                    @endfragment
                </div>
                HTML;
            }
        })->html();

        $this->assertStringContainsString('<span>Rows</span>', $html);
        $this->assertStringNotContainsString('type=transport|name=unsafe|name', $html);
    }

    public function test_duplicate_native_fragment_names_only_mark_the_first_occurrence()
    {
        $html = Livewire::test(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    @fragment('summary')
                        <span>First</span>
                    @endfragment

                    @fragment('summary')
                        <span>Second</span>
                    @endfragment
                </div>
                HTML;
            }
        })->html();

        $this->assertStringContainsString('<span>First</span>', $html);
        $this->assertStringContainsString('<span>Second</span>', $html);
        $this->assertSame(1, substr_count($html, $this->marker('FRAGMENT', 'summary')));
        $this->assertSame(1, substr_count($html, $this->marker('ENDFRAGMENT', 'summary')));
    }

    public function test_fragment_expression_is_evaluated_only_once()
    {
        $instance = new class extends Component {
            public static int $evaluations = 0;

            public function fragmentName(): string
            {
                static::$evaluations++;

                return 'summary';
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    @fragment($this->fragmentName())
                        <span>Summary</span>
                    @endfragment
                </div>
                HTML;
            }
        };

        $instance::$evaluations = 0;

        Livewire::test($instance);

        $this->assertSame(1, $instance::$evaluations);
    }

    protected function marker(string $type, string $name): string
    {
        $token = substr(hash('sha256', $name), 0, 16);

        return "<!--[if {$type}:type=transport|name={$name}|token={$token}|mode=morph]><![endif]-->";
    }
}
