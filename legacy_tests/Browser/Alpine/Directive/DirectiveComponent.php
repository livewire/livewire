<?php

namespace LegacyTests\Browser\Alpine\Directive;

use Livewire\Component as BaseComponent;

class DirectiveComponent extends BaseComponent
{
    public function render()
    {
        return
<<<'HTML'
<div>
    <div x-foo>
        <span x-text="value"></span>
    </div>

    <button dusk="button" wire:click="$refresh">
        refresh
    </button>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.directive('foo', function (el) {
                Alpine.bind(el, {
                    'x-data'() {
                        return {
                            value: false
                        }
                    },
                    'x-on:click'() {
                        this.value = !this.value;
                    }
                })
            })
        })
    </script>

</div>
HTML;
    }
}
