<?php

namespace Livewire\Features\SupportSubtreeHashes;

use Tests\BrowserTestCase;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['livewire.skip_unchanged.enabled' => true]);
        };
    }

    // Records how many placeholders each response carried, and how many requests went out...
    protected $spy = <<<'JS'
        window.__same = []
        window.__requests = 0
        Livewire.interceptRequest(({ onSuccess }) => {
            window.__requests++
            onSuccess(({ json }) => json.components.forEach(c => {
                let html = (c.effects.html || '') + (c.effects.islandFragments || []).join('')
                window.__same.push((html.match(/wire:same/g) || []).length)
            }))
        })
    JS;

    public function test_unchanged_keyed_subtrees_are_skipped_and_changed_ones_morph()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $title = 'one';

            public $items = ['a' => 'Alpha', 'b' => 'Beta', 'c' => 'Gamma'];

            public function rename() { $this->items['b'] = 'Bravo'; }

            public function render() { return <<<'HTML'
                <div>
                    <h1 dusk="title">{{ $title }}</h1>

                    <button wire:click="$set('title', 'two')" dusk="retitle">Retitle</button>
                    <button wire:click="rename" dusk="rename">Rename</button>

                    @foreach ($items as $key => $item)
                        <div wire:key="{{ $key }}" dusk="item-{{ $key }}">{{ $item }}<span hidden>{{ str_repeat('x', 1100) }}</span></div>
                    @endforeach
                </div>
            HTML; }
        })
            ->tap(fn ($b) => $b->script($this->spy))
            ->waitForLivewire()->click('@retitle')
            ->assertSeeIn('@title', 'two')
            ->assertScript('window.__same.at(-1)', 3)
            ->assertSeeIn('@item-a', 'Alpha')
            ->assertSeeIn('@item-b', 'Beta')
            ->waitForLivewire()->click('@rename')
            ->assertScript('window.__same.at(-1)', 2)
            ->assertSeeIn('@item-a', 'Alpha')
            ->assertSeeIn('@item-b', 'Bravo')
            ->assertSeeIn('@item-c', 'Gamma')
            ->assertScript('document.querySelectorAll("[wire\\\\:same]").length', 0);
    }

    public function test_reordered_subtrees_are_moved_without_a_resync()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $order = ['a', 'b', 'c', 'd', 'e', 'f'];

            // Like a sort that brings new rows in, drops some, and moves the rest...
            public function reverse() { $this->order = $this->order[0] === 'a' ? ['x', 'e', 'y', 'c', 'a', 'z'] : ['a', 'b', 'c', 'd', 'e', 'f']; }

            public function render() { return <<<'HTML'
                <div>
                    <button wire:click="reverse" dusk="reverse">Reverse</button>

                    <ul dusk="list">
                        @foreach ($order as $key)
                            @if ($key !== 'none')
                            <li wire:key="{{ $key }}" x-data="{ touched: false }" x-on:click="touched = true" dusk="item-{{ $key }}">
                                {{ strtoupper($key) }} <span x-text="touched ? 'touched' : ''"></span>
                                <span hidden>{{ str_repeat($key, 1100) }}</span>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            HTML; }
        })
            ->tap(fn ($b) => $b->script($this->spy))
            ->click('@item-c')
            ->assertSeeIn('@item-c', 'touched')
            ->waitForLivewire()->click('@reverse')
            ->pause(300)
            ->assertScript('[...document.querySelectorAll("li")].map(li => li.innerText.trim().charAt(0)).join("")', 'XEYCAZ')
            ->assertScript('window.__requests', 1)
            ->assertScript('document.querySelectorAll("[wire\\\\:same]").length', 0)
            // Moved, not rebuilt, so its Alpine state survives...
            ->assertSeeIn('@item-c', 'touched')
            ->waitForLivewire()->click('@reverse')
            ->pause(300)
            ->assertScript('[...document.querySelectorAll("li")].map(li => li.innerText.trim().charAt(0)).join("")', 'ABCDEF')
            ->assertScript('window.__requests', 2);
    }

    public function test_unchanged_children_of_a_changed_parent_are_still_skipped()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function render() { return <<<'HTML'
                <div>
                    <button wire:click="$set('count', {{ $count + 1 }})" dusk="bump">Bump</button>

                    <section wire:key="parent">
                        <p dusk="count">{{ $count }}</p>

                        <div wire:key="child" dusk="child">Child<span hidden>{{ str_repeat('c', 1100) }}</span></div>
                    </section>
                </div>
            HTML; }
        })
            ->tap(fn ($b) => $b->script($this->spy))
            ->waitForLivewire()->click('@bump')
            ->assertSeeIn('@count', '1')
            ->assertScript('window.__same.at(-1)', 1)
            ->assertSeeIn('@child', 'Child');
    }

    public function test_ignore_self_elements_keep_their_hash_in_step_with_their_markup()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $state = 'A';

            public function render() { return <<<'HTML'
                <div>
                    <button wire:click="$set('state', 'B')" dusk="to-b">B</button>
                    <button wire:click="$set('state', 'A')" dusk="to-a">A</button>

                    <div wire:key="box" wire:ignore.self>
                        <span dusk="state">{{ $state }}</span>
                        @if ($state === 'B') <em dusk="extra">extra</em> @endif
                        <span hidden>{{ str_repeat('s', 1100) }}</span>
                    </div>
                </div>
            HTML; }
        })
            ->tap(fn ($b) => $b->script($this->spy))
            ->waitForLivewire()->click('@to-b')
            ->assertSeeIn('@state', 'B')
            ->assertPresent('@extra')
            // Back to markup whose hash matches the box's original one: it must not be skipped...
            ->waitForLivewire()->click('@to-a')
            ->assertSeeIn('@state', 'A')
            ->assertMissing('@extra');
    }

    public function test_islands_skip_unchanged_subtrees_and_forget_the_hashes_around_them()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $count = 0;

            public function increment() { $this->count++; }

            public function render() { return <<<'HTML'
                <div>
                    <section wire:key="around">
                        @island
                            <button type="button" wire:click="increment" dusk="island-increment">Count: {{ $count }}</button>

                            <div wire:key="island-row" dusk="island-row">Row<span hidden>{{ str_repeat('i', 1100) }}</span></div>
                        @endisland

                        <span hidden>{{ str_repeat('o', 1100) }}</span>
                    </section>

                    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
                    <span dusk="root-count">Root: {{ $count }}</span>
                </div>
            HTML; }
        })
            ->tap(fn ($b) => $b->script($this->spy))
            ->waitForLivewire()->click('@island-increment')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertScript('window.__same.at(-1)', 1)
            ->assertSeeIn('@island-row', 'Row')
            // The island changed what's inside "around", so it can't be claimed any more...
            ->assertScript('document.querySelector("[wire\\\\:key=around]").hasAttribute("wire:hash")', false)
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@root-count', 'Root: 1')
            ->assertSeeIn('@island-increment', 'Count: 1')
            ->assertScript('document.querySelectorAll("[wire\\\\:same]").length', 0);
    }

    public function test_reordered_subtrees_inside_an_island_are_moved_without_a_resync()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $order = ['a', 'b', 'c', 'd', 'e', 'f'];

            // Like a sort that brings new rows in, drops some, and moves the rest...
            public function reverse() { $this->order = ['x', 'e', 'y', 'b', 'a', 'z']; }

            public function render() { return <<<'HTML'
                <div>
                    @island
                        <button type="button" wire:click="reverse" dusk="reverse">Reverse</button>

                        <ul>
                            @foreach ($order as $key)
                                @if ($key !== 'none')
                                <li wire:key="{{ $key }}" x-data="{ touched: false }" x-on:click="touched = true" dusk="item-{{ $key }}">
                                    {{ strtoupper($key) }} <span x-text="touched ? 'touched' : ''"></span>
                                    <span hidden>{{ str_repeat($key, 1100) }}</span>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    @endisland
                </div>
            HTML; }
        })
            ->tap(fn ($b) => $b->script($this->spy))
            ->click('@item-b')
            ->waitForLivewire()->click('@reverse')
            ->pause(300)
            ->assertScript('[...document.querySelectorAll("li")].map(li => li.innerText.trim().charAt(0)).join("")', 'XEYBAZ')
            ->assertScript('window.__requests', 1)
            ->assertScript('document.querySelectorAll("[wire\\\\:same]").length', 0)
            ->assertSeeIn('@item-b', 'touched');
    }

    public function test_markup_morphed_by_a_package_is_never_skipped()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>

                    <div wire:key="panel">
                        <p dusk="text">Server</p>
                        <span hidden>{{ str_repeat('p', 1100) }}</span>
                    </div>
                </div>
            HTML; }
        })
            ->tap(fn ($b) => $b->script($this->spy))
            // Like a package morphing its own partial, without telling Livewire...
            ->tap(fn ($b) => $b->script('let el = document.querySelector("[dusk=text]"); let to = el.cloneNode(); to.textContent = "Package"; Alpine.morph(el, to)'))
            ->assertSeeIn('@text', 'Package')
            ->waitForLivewire()->click('@refresh')
            ->assertScript('window.__same.at(-1)', 0)
            ->assertSeeIn('@text', 'Server');
    }

    public function test_markup_morphed_between_markers_by_a_package_is_never_skipped()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>

                    <div wire:key="panel" dusk="panel">
                        <!--start--><p>Server</p><!--end-->
                        <span hidden>{{ str_repeat('p', 1100) }}</span>
                    </div>
                </div>
            HTML; }
        })
            ->tap(fn ($b) => $b->script($this->spy))
            ->tap(fn ($b) => $b->script('let markers = [...document.querySelector("[dusk=panel]").childNodes].filter(n => n.nodeType === 8); Alpine.morphBetween(markers[0], markers[1], "<p>Package</p>")'))
            ->assertSeeIn('@panel', 'Package')
            ->waitForLivewire()->click('@refresh')
            ->assertScript('window.__same.at(-1)', 0)
            ->assertSeeIn('@panel', 'Server');
    }

    public function test_markup_changed_outside_livewire_is_reported_with_mark_changed()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>

                    <div wire:key="panel" wire:ignore.self dusk="panel">
                        <p dusk="text">Server</p>
                        <span hidden>{{ str_repeat('p', 1100) }}</span>
                    </div>
                </div>
            HTML; }
        })
            ->tap(fn ($b) => $b->script($this->spy))
            // Like a package morphing its own partial: it changes the markup, then reports it...
            ->tap(fn ($b) => $b->script('document.querySelector("[dusk=text]").textContent = "Changed"; Livewire.markChanged(document.querySelector("[dusk=text]"))'))
            ->waitForLivewire()->click('@refresh')
            ->assertScript('window.__same.at(-1)', 0)
            ->assertSeeIn('@text', 'Server');
    }
}
