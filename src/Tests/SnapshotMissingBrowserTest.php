<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class SnapshotMissingBrowserTest extends \Tests\BrowserTestCase
{
    // https://github.com/livewire/livewire/discussions/9037
    public function test_scenario_1_different_root_element_with_lazy_passing()
    {
        Livewire::visit([
            new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child lazy />
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>child</div>
                    HTML;
                }
            },
        ])
            ->waitForLivewire()
            ->waitForText('child')
            ->assertSee('child')
            ->assertConsoleLogHasNoErrors();
    }

    // https://github.com/livewire/livewire/discussions/9037
    public function test_scenario_1_different_root_element_with_lazy_failing()
    {
        Livewire::visit([
            new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child lazy />
                    </div>
                    HTML;
                }
            },
            'child' => new class () extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <section>child</section>
                    HTML;
                }
            },
        ])
            ->waitForLivewire()
            ->waitForText('child')
            ->assertSee('child')
            ->assertConsoleLogHasNoErrors();
    }
}
