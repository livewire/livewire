<?php

namespace Livewire\Tests;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class DependentSelectBrowserTest extends BrowserTestCase
{
    /** @test */
    public function updates_dependent_select_options_correctly()
    {
        Livewire::visit(new class extends Component {
            public $parent = 'foo';

            public $child = 'bar';

            protected $options = [
                'foo' => [
                    'bar',
                ],
                'baz' => [
                    'qux',
                ],
            ];

            #[Computed]
            public function parentOptions(): array
            {
                return array_keys($this->options);
            }

            #[Computed]
            public function childOptions(): array
            {
                return $this->options[$this->parent];
            }

            public function render(): string
            {
                return <<<'blade'
                    <div>
                        <select wire:model.live="parent" dusk="parent">
                            @foreach($this->parentOptions as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>

                        <select wire:model="child" dusk="child">
                            <option value>Select</option>
                            @foreach($this->childOptions as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                blade;
            }
        })
            ->waitForLivewire()
            ->select('@parent', 'baz')
            ->assertSelected('@child', '')
            ->select('@parent', 'foo')
            ->assertSelected('@child', 'bar');
    }

}
