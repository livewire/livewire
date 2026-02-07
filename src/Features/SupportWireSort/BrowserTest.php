<?php

namespace Livewire\Features\SupportWireSort;

use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_sort_id_is_passed_as_third_parameter_to_sort_handler()
    {
        Livewire::visit(new class extends Component {
            public $result = '';

            public function sortItem($item, $position, $group)
            {
                $this->result = "item:{$item},position:{$position},group:{$group}";
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <ul dusk="sortable" wire:sort="sortItem" wire:sort:group="todos" wire:sort:id="column-3">
                        <li wire:sort:item="item-1">Item 1</li>
                        <li wire:sort:item="item-2">Item 2</li>
                        <li wire:sort:item="item-3">Item 3</li>
                    </ul>

                    <div dusk="result">{{ $result }}</div>
                </div>
                HTML;
            }
        })
        ->tap(function ($b) {
            // Simulate a sort by triggering the SortableJS onSort callback.
            // SortableJS stores its instance on the element as a property starting with "Sortable".
            $b->script(<<<'JS'
                let el = document.querySelector('[dusk="sortable"]')
                let item = el.querySelector('[wire\\:sort\\:item="item-2"]')

                // Move the element in the DOM to position 0 (before item-1)
                el.insertBefore(item, el.firstElementChild)

                // Find the SortableJS instance on the element
                let key = Object.keys(el).find(k => k.startsWith('Sortable'))
                let instance = el[key]

                instance.options.onSort({
                    item: item,
                    from: el,
                    to: el,
                    target: el,
                    newIndex: 0,
                    oldIndex: 1,
                })
            JS);
        })
        ->waitForTextIn('@result', 'item:item-2,position:0,group:column-3')
        ->assertSeeIn('@result', 'item:item-2,position:0,group:column-3');
    }

    public function test_wire_sort_works_without_sort_id()
    {
        Livewire::visit(new class extends Component {
            public $result = '';

            public function sortItem($item, $position)
            {
                $this->result = "item:{$item},position:{$position}";
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <ul dusk="sortable" wire:sort="sortItem">
                        <li wire:sort:item="item-1">Item 1</li>
                        <li wire:sort:item="item-2">Item 2</li>
                    </ul>

                    <div dusk="result">{{ $result }}</div>
                </div>
                HTML;
            }
        })
        ->tap(function ($b) {
            $b->script(<<<'JS'
                let el = document.querySelector('[dusk="sortable"]')
                let item = el.querySelector('[wire\\:sort\\:item="item-2"]')

                el.insertBefore(item, el.firstElementChild)

                let key = Object.keys(el).find(k => k.startsWith('Sortable'))
                let instance = el[key]

                instance.options.onSort({
                    item: item,
                    from: el,
                    to: el,
                    target: el,
                    newIndex: 0,
                    oldIndex: 1,
                })
            JS);
        })
        ->waitForTextIn('@result', 'item:item-2,position:0')
        ->assertSeeIn('@result', 'item:item-2,position:0');
    }
}
