<?php

namespace Livewire\Features\SupportLifecycleHooks;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_livewire_updated_hook_with_dynamic_properties()
    {
        Livewire::visit(new class extends Component {
            public array $foo = ['bar' => []];
            public string $resultUpdatedFooBarHook = '';
            public string $resultUpdatedHook = '';

            public function updated($propertyName): void
            {
                $this->resultUpdatedHook = 'YAY';
            }

            public function updatedFooBar(): void
            {
                $this->resultUpdatedFooBarHook = 'YAY';
            }

            #[Layout('layouts.app')]
            function render()
            {
                return <<<'blade'
                    <div>
                        @for($i=0; $i<5; $i++)
                            <label>
                                <input type="checkbox" wire:model.live="foo.bar" value="{{ $i }}" dusk="{{ 'checkbox_'.$i }}">
                                {{ $i }}
                            </label>
                        @endfor
                        @if(! empty($this->resultUpdatedHook))
                            <span dusk="resultUpdatedHook">{{ $this->resultUpdatedHook }}</span>
                        @endif
                        @if(! empty($this->resultUpdatedFooBarHook))
                            <span dusk="resultUpdatedFooBarHook">{{ $this->resultUpdatedFooBarHook }}</span>
                        @endif
                    </div>
                blade;
            }
        })
            ->waitForLivewire()->click('@checkbox_2')
            ->assertPresent('@resultUpdatedHook')
            ->assertPresent('@resultUpdatedFooBarHook')
        ;
    }
}
