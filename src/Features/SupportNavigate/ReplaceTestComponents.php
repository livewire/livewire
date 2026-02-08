<?php

namespace Livewire\Features\SupportNavigate;

use Livewire\Component;
use Livewire\Attributes\Layout;

class ReplaceTestFirstPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On first page</div>
            <a href="/replace-test-second" wire:navigate dusk="link.to.second">To Second (Push)</a>
        </div>
        HTML;
    }
}

class ReplaceTestSecondPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On second page</div>
            <a href="/replace-test-third" wire:navigate.replace dusk="link.to.third.replace">To Third (Replace)</a>
        </div>
        HTML;
    }
}

class ReplaceTestThirdPage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On third page</div>
        </div>
        HTML;
    }
}

class FormPageWithReplace extends Component
{
    public $name = '';

    public function submit()
    {
        return $this->redirect('/replace-test-success', navigate: true, replace: true);
    }

    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Fill Form</div>
            <input wire:model="name" dusk="name-input">
            <button wire:click="submit" dusk="submit-button">Submit</button>
        </div>
        HTML;
    }
}

class SuccessPageWithReplace extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Success Page</div>
        </div>
        HTML;
    }
}

class ScrollPageWithReplace extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Scroll Test Page</div>
            <div style="height: 2000px;"></div>
            <div dusk="scroll-target">Target Element</div>
            <a href="/replace-test-second" wire:navigate.replace.preserve-scroll dusk="link.replace.preserve">
                Navigate with Replace & Preserve Scroll
            </a>
        </div>
        HTML;
    }
}

class HoverPrefetchReplacePage extends Component
{
    #[Layout('test-views::layout')]
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Hover Prefetch Test</div>
            <a href="/replace-test-second" wire:navigate.replace.hover dusk="link.replace.hover">
                Navigate with Replace & Hover Prefetch
            </a>
        </div>
        HTML;
    }
}
