<?php

namespace Livewire\Features\SupportNestingComponents;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_add_new_components()
    {
        Livewire::visit([
            Page::class,
            'first-component' => FirstComponent::class,
            'second-component' => SecondComponent::class,
            'third-component' => ThirdComponent::class,
        ])
            ->assertSee('Page')

            ->waitForLivewire()->click('@add-first')
            ->assertSee('First Component Rendered')
            ->assertDontSee('Second Component Rendered')
            ->assertDontSee('Third Component Rendered')

            ->waitForLivewire()->click('@add-second')
            ->assertSee('First Component Rendered')
            ->assertSee('Second Component Rendered')
            ->assertDontSee('Third Component Rendered')

            ->waitForLivewire()->click('@add-third')
            ->assertSee('First Component Rendered')
            ->assertSee('Second Component Rendered')
            ->assertSee('Third Component Rendered')

            ->waitForLivewire()->click('@remove-second')
            ->assertSee('First Component Rendered')
            ->assertDontSee('Second Component Rendered')
            ->assertSee('Third Component Rendered')
        ;
    }

    public function test_nested_components_do_not_error_with_empty_elements_on_page()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div>
                        </div>

                        <button type="button" wire:click="$refresh" dusk="refresh">
                            Refresh
                        </button>

                        <livewire:child />

                        <div>
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div dusk="child">
                        Child
                    </div>
                    HTML;
                }
            },
        ])
        ->assertPresent('@child')
        ->assertSeeIn('@child', 'Child')
        ->waitForLivewire()->click('@refresh')
        ->pause(500)
        ->assertPresent('@child')
        ->assertSeeIn('@child', 'Child')
        ->waitForLivewire()->click('@refresh')
        ->pause(500)
        ->assertPresent('@child')
        ->assertSeeIn('@child', 'Child')
        ;
    }

    public function test_nested_components_do_not_error_when_parent_has_custom_layout_and_default_layout_does_not_exist()
    {
        config()->set('livewire.layout', '');

        Livewire::visit([
            new class extends Component {
                #[Layout('layouts.app')]
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button type="button" wire:click="$refresh" dusk="refresh">
                            Refresh
                        </button>
                        <livewire:child />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div dusk="child">
                        Child
                    </div>
                    HTML;
                }
            },
        ])
            ->assertPresent('@child')
            ->assertSeeIn('@child', 'Child')
            ->waitForLivewire()->click('@refresh')
            ->assertPresent('@child')
            ->assertSeeIn('@child', 'Child')
        ;
    }

    public function test_nested_components_do_not_error_when_child_deleted()
    {
        Livewire::visit([
            new class extends Component {
                public $children = [
                    'one',
                    'two'
                ];

                public function deleteChild($name) {
                    unset($this->children[array_search($name, $this->children)]);
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div>
                        </div>

                        @foreach($this->children as $key => $name)
                            <livewire:child wire:key="{{ $key }}" :name="$name" />
                        @endforeach

                        <div>
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public $name = '';

                public function render()
                {
                    return <<<'HTML'
                    <div dusk="child-{{ $name }}">
                        {{ $name }}

                        <button dusk="delete-{{ $name }}" wire:click="$parent.deleteChild('{{ $name }}')">Delete</button>
                    </div>
                    HTML;
                }
            },
        ])
        ->assertPresent('@child-one')
        ->assertSeeIn('@child-one', 'one')
        ->waitForLivewire()->click('@delete-one')
        ->assertNotPresent('@child-one');
    }

    public function test_lazy_nested_components_do_not_call_boot_method_twice()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <div>Page</div>
                        <livewire:nested-boot-component lazy/>
                    </div>
                    HTML;
                }
            },
            'nested-boot-component' => new class extends Component {
                public $bootCount = 0;

                public function boot()
                {
                    $this->increment();
                }

                public function increment()
                {
                    $this->bootCount ++;
                }

                public function render()
                {
                    return '<div>Boot count: {{ $bootCount }}</div>';
                }

            }])
            ->assertSee('Page')
            ->waitForText('Boot count: 1');
        ;
    }

    public function test_nested_components_can_be_removed_and_readded_to_dom()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div id="root" x-data>
                        <button dusk="button" @click="
                            nestedElement = document.getElementById('removable')
                            nestedElement.remove();

                            setTimeout(() => {
                                document.getElementById('root').appendChild(nestedElement);
                                window.readded = true;
                            }, 750);
                        ">remove and re-add child</button>

                        <div id="removable">
                            <livewire:child/>
                        </div>
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public $clicked = false;

                public function render()
                {
                    return <<<'HTML'
                    <div dusk="child">
                        <button dusk="child-button" wire:click="$set('clicked', true)">child button</button>
                        <p dusk="child-text">@js($clicked)</p>
                    </div>
                    HTML;
                }
            },
        ])
        ->assertPresent('@child')
        ->assertScript('Livewire.all().length', 2)
        ->click('@button')
        ->waitUntil('window.readded', 5, true)
        ->assertPresent('@child')
        ->assertScript('Livewire.all().length', 2)
        ->assertSeeIn('@child-text', 'false')
        ->waitForLivewire()->click('@child-button')
        ->assertSeeIn('@child-text', 'true');
    }

    public function test_can_submit_form_using_parent_action_without_permenantly_disabling_form()
    {
        Livewire::visit([
            new class extends Component
            {
                public $textFromChildComponent;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child />

                        <span dusk="output">{{ $textFromChildComponent }}</span>
                    </div>
                    HTML;
                }

                public function submit($text)
                {
                    $this->textFromChildComponent = $text;
                }
            },
            'child' => new class extends Component
            {
                public $text;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <form wire:submit="$parent.submit($wire.text)">
                            <input type="text" name="test" wire:model="text" dusk="input" />
                            <button type="submit" dusk="submit-btn">submit</button>
                        </form>
                    </div>
                    HTML;
                }
            }
        ])
            ->type('@input', 'hello')
            ->click('@submit-btn')
            ->waitForTextIn('@output', 'hello')
            ->assertAttributeMissing('@input', 'readonly')
            ->assertAttributeMissing('@submit-btn', 'disabled');
    }

    public function test_can_listen_to_multiple_events_using_at_directive_attribute_from_child_component()
    {
        Livewire::visit([
            new class extends Component
            {
                public $text;

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <livewire:child @foo="foo" @bar="bar" />
                        <span>{{ $text }}</span>
                    </div>
                    HTML;
                }

                public function foo()
                {
                    $this->text = 'foo';
                }

                public function bar()
                {
                    $this->text = 'bar';
                }
            },
            'child' => new class extends Component
            {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button type="button" wire:click="$dispatch('foo')" dusk="dispatch-foo-event-btn">
                            Dispatch Foo
                        </button>
                        <button type="button" wire:click="$dispatch('bar')" dusk="dispatch-bar-event-btn">
                            Dispatch Bar
                        </button>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewire()->click('@dispatch-bar-event-btn')
            ->assertSee('bar')
            ->waitForLivewire()->click('@dispatch-foo-event-btn')
            ->assertSee('foo');
    }
}

class Page extends Component
{
    public $components = [];

    public function add($item)
    {
        $this->components[$item] = [];
    }

    public function remove($item)
    {
        unset($this->components[$item]);
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Page</div>

            @foreach($components as $component => $params)
                @livewire($component, $params, key($component))
            @endforeach

            <div>
                <button dusk="add-first" wire:click="add('first-component')">Add first-component</button>
                <button dusk="add-second" wire:click="add('second-component')">Add second-component</button>
                <button dusk="add-third" wire:click="add('third-component')">Add third-component</button>
            </div>

            <div>
                <button dusk="remove-first" wire:click="remove('first-component')">Remove first-component</button>
                <button dusk="remove-second" wire:click="remove('second-component')">Remove second-component</button>
                <button dusk="remove-third" wire:click="remove('third-component')">Remove third-component</button>
            </div>
        </div>
        HTML;
    }
}

class FirstComponent extends Component
{
    public function render()
    {
        return '<div>First Component Rendered</div>';
    }
}

class SecondComponent extends Component
{
    public function render()
    {
        return '<div>Second Component Rendered</div>';
    }
}

class ThirdComponent extends Component
{
    public function render()
    {
        return '<div>Third Component Rendered</div>';
    }
}

class BootPage extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>Page</div>

            <livewire:nested-boot-component lazy/>
        </div>
        HTML;
    }
}

class NestedBootComponent extends Component
{
    public $bootCount = 0;

    public function boot()
    {
        $this->increment();
    }

    public function increment()
    {
        $this->bootCount ++;
    }

    public function render()
    {
        return '<div>Boot count: {{ $bootCount }}</div>';
    }
}
