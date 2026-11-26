<?php

namespace Livewire\Features\SupportIslands;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class IslandPartsBrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['app.debug' => true]);
        };
    }

    public function test_parts_preserve_loaded_pages_cursor_focus_alpine_and_child_component_identity()
    {
        Livewire::visit([
            new class extends Component {
                public $cursor = 2;
                public $rows = [['id' => 1, 'name' => 'Row 1'], ['id' => 2, 'name' => 'Row 2']];

                public function more()
                {
                    $rows = array_map(fn ($id) => ['id' => $id, 'name' => "Row {$id}"], range($this->cursor + 1, $this->cursor + 2));
                    $this->cursor += 2;
                    $this->renderIsland('users', mode: 'append', with: ['rows' => $rows]);
                    $this->skipRender();
                }

                public function change($id = 1, $mode = 'morph')
                {
                    $this->renderIslandPart('users', 'row', 'row-' . $id, with: ['row' => ['id' => $id, 'name' => 'Updated ' . $id]], mode: $mode);
                    $this->skipRender();
                }

                public function remove($id)
                {
                    $this->removeIslandPart('users', 'row', 'row-' . $id);
                    $this->skipRender();
                }

                public function render() { return <<<'HTML'
                <div>
                    <p dusk="base">{{ microtime(true) }}</p>
                    <button wire:click="more" dusk="more">More</button>
                    <button wire:click="change(0, 'prepend')" dusk="prepend">Prepend</button>
                    <button wire:click="change(7, 'append')" dusk="append">Append</button>
                    <button wire:click="remove(4)" dusk="remove-boundary">Remove boundary</button>
                    <button wire:click="remove(1)" dusk="remove-first">Remove first</button>
                    <ul dusk="list">@island(name: 'users')
                        @foreach($rows as $row)
                            @islandPart(name: 'row')
                                <li wire:key="row-{{ $row['id'] }}" dusk="row-{{ $row['id'] }}" x-data="{ draft: '' }">
                                    <span dusk="name-{{ $row['id'] }}">{{ $row['name'] }}</span>
                                    <input dusk="input-{{ $row['id'] }}" x-model="draft">
                                    <span dusk="draft-{{ $row['id'] }}" x-text="draft"></span>
                                    <livewire:part-child :row-id="$row['id']" :key="'child-'.$row['id']" />
                                </li>
                            @endislandPart
                        @endforeach
                    @endisland</ul>
                </div>
                HTML; }
            },
            'part-child' => new class extends Component {
                public $rowId;
                public $count = 0;
                public function increment() { $this->count++; }
                public function render() { return <<<'HTML'
                <section dusk="child-{{ $rowId }}">
                    <button wire:click="increment" dusk="child-increment-{{ $rowId }}">Increment</button>
                    <span dusk="child-count-{{ $rowId }}">{{ $count }}</span>
                </section>
                HTML; }
            },
        ])
        ->tap(fn ($browser) => $browser->script('window.initialBase = document.querySelector(\'[dusk="base"]\').textContent'))
        ->waitForLivewire()->click('@child-increment-1')
        ->assertSeeIn('@child-count-1', '1')
        ->waitForLivewire()->click('@more')
        ->assertPresent('@row-4')
        ->type('@input-1', 'keep this draft')
        ->tap(function ($browser) {
            $browser->script('window.first = document.querySelector(\'[dusk="row-1"]\'); window.second = document.querySelector(\'[dusk="row-2"]\'); window.child = document.querySelector(\'[dusk="child-1"]\'); window.focused = document.activeElement; window.Livewire.all()[0].$wire.change()');
        })
        ->waitForTextIn('@name-1', 'Updated 1')
        ->assertScript('document.querySelector(\'[dusk="row-1"]\') === window.first', true)
        ->assertScript('document.querySelector(\'[dusk="row-2"]\') === window.second', true)
        ->assertScript('document.querySelector(\'[dusk="child-1"]\') === window.child', true)
        ->assertScript('document.activeElement === window.focused', true)
        ->assertInputValue('@input-1', 'keep this draft')
        ->assertSeeIn('@draft-1', 'keep this draft')
        ->assertSeeIn('@child-count-1', '1')
        ->assertPresent('@row-3')
        ->assertPresent('@row-4')
        ->waitForLivewire()->click('@prepend')
        ->assertScript('document.querySelector(\'[dusk="list"]\').firstElementChild.getAttribute("dusk")', 'row-0')
        ->waitForLivewire()->click('@append')
        ->assertScript('document.querySelector(\'[dusk="list"]\').lastElementChild.getAttribute("dusk")', 'row-7')
        ->waitForLivewire()->click('@remove-boundary')
        ->assertMissing('@row-4')
        ->waitForLivewire()->click('@more')
        ->assertPresent('@row-5')
        ->assertPresent('@row-6')
        ->assertPresent('@row-0')
        ->assertPresent('@row-7')
        ->assertSeeIn('@child-count-1', '1')
        ->tap(fn ($browser) => $browser->script('window.childCount = window.Livewire.all().length'))
        ->waitForLivewire()->click('@remove-first')
        ->assertMissing('@child-1')
        ->waitUntil('window.Livewire.all().length === window.childCount - 1')
        ->assertScript('document.querySelector(\'[dusk="base"]\').textContent === window.initialBase', true)
        ->assertConsoleLogHasNoErrors();
    }

    public function test_parts_use_table_context_for_morph_insert_and_remove()
    {
        Livewire::visit(new class extends Component {
            public $rows = [1, 2];
            public function change($id, $mode)
            {
                $this->renderIslandPart('table', 'row', 'row-' . $id, with: ['id' => $id, 'label' => 'Updated'], mode: $mode);
                $this->skipRender();
            }
            public function render() { return <<<'HTML'
            <div>
                <button wire:click="change(1, 'morph')" dusk="edit">Edit</button>
                <button wire:click="change(3, 'append')" dusk="add">Add</button>
                <button wire:click="change(2, 'remove')" dusk="remove">Remove</button>
                <table><tbody>@island(name: 'table')
                    @foreach($rows as $id)
                        @islandPart('row')
                            <tr wire:key="row-{{ $id }}" dusk="row-{{ $id }}"><td>{{ $label ?? 'Original' }}</td></tr>
                        @endislandPart
                    @endforeach
                @endisland</tbody></table>
            </div>
            HTML; }
        })
        ->waitForLivewire()->click('@edit')
        ->assertSeeIn('@row-1', 'Updated')
        ->waitForLivewire()->click('@add')
        ->assertSeeIn('@row-3', 'Updated')
        ->waitForLivewire()->click('@remove')
        ->assertMissing('@row-2')
        ->assertScript('document.querySelectorAll("tbody > tr").length', 2)
        ->assertConsoleLogHasNoErrors();
    }

    public function test_invalid_parts_report_errors_without_sticking_loading_or_blocking_later_requests()
    {
        Livewire::visit(new class extends Component {
            public $invalid = false;
            public function change($target = 'row-1', $mode = 'morph', $invalid = false)
            {
                $this->renderIslandPart('users', 'row', $target, with: ['invalid' => $invalid, 'label' => 'Updated'], mode: $mode);
                $this->skipRender();
            }
            public function render() { return <<<'HTML'
            <div>
                <button wire:click="change('missing')" wire:loading.attr="disabled" dusk="missing">Missing</button>
                <button wire:click="change('row-1', 'append')" dusk="duplicate">Duplicate</button>
                <button wire:click="change('row-1', 'morph', true)" dusk="malformed">Malformed</button>
                <button wire:click="change" dusk="valid">Valid</button>
                <ul>@island(name: 'users')
                    @islandPart('row')
                        @if($invalid)
                            <li data-island-part="row-1">Bad</li><li>Extra root</li>
                        @else
                            <li data-island-part="row-1" dusk="row">{{ $label ?? 'Original' }}</li>
                        @endif
                    @endislandPart
                @endisland</ul>
            </div>
            HTML; }
        })
        ->tap(function ($browser) {
            $browser->script('window.partErrors = []; window.addEventListener("error", event => { window.partErrors.push(event.message); event.preventDefault() })');
        })
        ->waitForLivewire()->click('@missing')
        ->assertEnabled('@missing')
        ->assertSeeIn('@row', 'Original')
        ->waitForLivewire()->click('@duplicate')
        ->assertSeeIn('@row', 'Original')
        ->waitForLivewire()->click('@malformed')
        ->assertSeeIn('@row', 'Original')
        ->assertScript('window.partErrors.length', 3)
        ->waitForLivewire()->click('@valid')
        ->assertSeeIn('@row', 'Updated');
    }

    public function test_part_targets_are_scoped_to_their_own_component_and_nearest_island()
    {
        Livewire::visit([
            new class extends Component {
                public function change($target = 'shared')
                {
                    $this->renderIslandPart('users', 'row', $target, with: ['label' => 'Updated']);
                    $this->skipRender();
                }
                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="change" dusk="edit">Edit</button>
                    <button wire:click="change('child-only')" dusk="child-target">Child target</button>
                    <ul>@island(name: 'users')
                        @islandPart('row')
                            <li data-island-part="shared" dusk="own">{{ $label ?? 'Own' }}</li>
                        @endislandPart
                        @island(name: 'nested')
                            <li data-island-part="shared" dusk="nested">Nested</li>
                        @endisland
                        <li><livewire:part-child :key="'child'" /></li>
                    @endisland</ul>
                    @island(name: 'other')
                        @islandPart('row')
                            <p data-island-part="shared" dusk="other">Other</p>
                        @endislandPart
                    @endisland
                </div>
                HTML; }
            },
            'part-child' => new class extends Component {
                public function render() { return <<<'HTML'
                <section>
                    <span data-island-part="child-only" dusk="child-owned">Child</span>
                    <span data-island-part="shared">Shared child target</span>
                </section>
                HTML; }
            },
        ])
        ->tap(function ($browser) {
            $browser->script('window.partErrors = []; window.addEventListener("error", event => { window.partErrors.push(event.message); event.preventDefault() })');
        })
        ->waitForLivewire()->click('@edit')
        ->assertSeeIn('@own', 'Updated')
        ->assertSeeIn('@other', 'Other')
        ->assertSeeIn('@nested', 'Nested')
        ->waitForLivewire()->click('@child-target')
        ->assertSeeIn('@child-owned', 'Child')
        ->assertScript('window.partErrors.length', 1)
        ->assertScript('window.partErrors[0].includes("found 0")', true);
    }

    public function test_part_targets_support_quotes_brackets_backslashes_spaces_and_unicode()
    {
        Livewire::visit(new class extends Component {
            public $target = 'row-"[]\\ space é';
            public function change()
            {
                $this->renderIslandPart('users', 'row', $this->target, with: ['label' => 'Updated']);
                $this->skipRender();
            }
            public function render() { return <<<'HTML'
            <div>
                <button wire:click="change" dusk="edit">Edit</button>
                <ul>@island(name: 'users')
                    @islandPart('row')
                        <li wire:key="{{ $target }}" dusk="row">{{ $label ?? 'Original' }}</li>
                    @endislandPart
                @endisland</ul>
            </div>
            HTML; }
        })
        ->waitForLivewire()->click('@edit')
        ->assertSeeIn('@row', 'Updated')
        ->assertConsoleLogHasNoErrors();
    }
}
