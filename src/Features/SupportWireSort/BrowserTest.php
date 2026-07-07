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
                    <ul dusk="sortable" wire:sort="sortItem" wire:sort:group="todos" wire:sort:group-id="column-3">
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

    public function test_wire_sort_position_is_correct_when_non_sortable_siblings_are_present()
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
                        <li wire:sort:ignore>Non-sortable A</li>
                        <li wire:sort:item="item-2">Item 2</li>
                        <li wire:sort:ignore>Non-sortable B</li>
                        <li wire:sort:item="item-3">Item 3</li>
                    </ul>

                    <div dusk="result">{{ $result }}</div>
                </div>
                HTML;
            }
        })
        ->tap(function ($b) {
            $b->script(<<<'JS'
                let el = document.querySelector('[dusk="sortable"]')
                let item = el.querySelector('[wire\\:sort\\:item="item-1"]')

                // Move item-1 to the end (after all other children)
                // Resulting DOM: [ignored-a, item-2, ignored-b, item-3, item-1]
                el.appendChild(item)

                let key = Object.keys(el).find(k => k.startsWith('Sortable'))
                let instance = el[key]

                // newIndex is 4 (raw DOM index), but the correct sortable-item
                // position should be 2 (item-1 is the 3rd sortable item: [item-2, item-3, item-1])
                instance.options.onSort({
                    item: item,
                    from: el,
                    to: el,
                    target: el,
                    newIndex: 4,
                    oldIndex: 0,
                })
            JS);
        })
        ->waitForTextIn('@result', 'item:item-1,position:2')
        ->assertSeeIn('@result', 'item:item-1,position:2');
    }

    public function test_wire_sort_item_id_works_when_added_to_an_already_rendered_element()
    {
        Livewire::visit(new class extends Component {
            public $enabled = false;
            public $result = '';

            public function enable()
            {
                $this->enabled = true;
            }

            public function sortItem($item, $position)
            {
                $this->result = "item:{$item},position:{$position}";
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button type="button" dusk="enable" wire:click="enable">Enable</button>

                    <ul dusk="sortable" wire:sort="sortItem">
                        <li wire:key="item-1" @if ($enabled) wire:sort:item="item-1" @endif>Item 1</li>
                        <li wire:key="item-2" @if ($enabled) wire:sort:item="item-2" @endif>Item 2</li>
                    </ul>

                    <div dusk="result">{{ $result }}</div>
                </div>
                HTML;
            }
        })
        ->click('@enable')
        ->waitForTextIn('[wire\\:key="item-2"]', 'Item 2')
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

    public function test_wire_sort_can_be_toggled_on_and_off()
    {
        Livewire::visit(new class extends Component {
            public $editing = false;
            public $result = '';

            public function toggle()
            {
                $this->editing = ! $this->editing;
            }

            public function sortItem($item, $position)
            {
                $this->result = "item:{$item},position:{$position}";
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button type="button" dusk="toggle" wire:click="toggle">
                        {{ $editing ? 'Save' : 'Edit' }}
                    </button>

                    <ul dusk="sortable" @if ($editing) wire:sort="sortItem" @endif>
                        <li wire:key="item-1" wire:sort:item="item-1">Item 1</li>
                        <li wire:key="item-2" wire:sort:item="item-2">Item 2</li>
                    </ul>

                    <div dusk="result">{{ $result }}</div>
                </div>
                HTML;
            }
        })
        ->waitForText('Edit')
        ->click('@toggle')
        ->waitForText('Save')
        ->tap(function ($b) {
            // Turning wire:sort on should make the already-rendered <ul> sortable...
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
        ->assertSeeIn('@result', 'item:item-2,position:0')
        ->click('@toggle')
        ->waitForText('Edit')
        ->tap(function ($b) {
            // Turning wire:sort back off should tear down the Sortable instance
            // entirely, rather than leaving it silently still active. SortableJS's
            // own destroy() nulls out its instance reference on the element rather
            // than deleting the key, so check the value rather than the key...
            $sortableInstanceWasDestroyed = $b->script(<<<'JS'
                let el = document.querySelector('[dusk="sortable"]')
                let key = Object.keys(el).find(k => k.startsWith('Sortable'))

                return key === undefined || el[key] === null
            JS)[0];

            $this->assertTrue($sortableInstanceWasDestroyed, 'Expected the Sortable instance to be destroyed after wire:sort was removed.');
        })
        ->assertSeeIn('@result', 'item:item-2,position:0');
    }

    public function test_wire_sort_item_id_is_passed_for_lazy_child_components()
    {
        Livewire::visit([
            new class extends Component {
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
                            <livewire:child wire:key="item-1" wire:sort:item="item-1" lazy>
                                Item 1
                            </livewire:child>

                            <livewire:child wire:key="item-2" wire:sort:item="item-2" lazy>
                                Item 2
                            </livewire:child>
                        </ul>

                        <div dusk="result">{{ $result }}</div>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function placeholder()
                {
                    return <<<'HTML'
                    <li>Loading...</li>
                    HTML;
                }

                public function render()
                {
                    return <<<'HTML'
                    <li {{ $attributes }}>
                        Child {{ $slot }}
                    </li>
                    HTML;
                }
            },
        ])
        ->waitForText('Child Item 2')
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
