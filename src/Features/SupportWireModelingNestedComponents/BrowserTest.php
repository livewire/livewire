<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Livewire\Livewire;
use Sushi\Sushi;

/** @group morphing */
class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_bind_a_property_from_parent_to_property_from_child()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public $foo = 0;

                public function render() { return <<<'HTML'
                <div>
                    <span dusk="parent">Parent: {{ $foo }}</span>
                    <span x-text="$wire.foo" dusk="parent.ephemeral"></span>

                    <livewire:child wire:model="foo" />

                    <button wire:click="$refresh" dusk="refresh">refresh</button>
                </div>
                HTML; }
            },
            'child' => new class extends \Livewire\Component {
                #[BaseModelable]
                public $bar;

                public function render() { return <<<'HTML'
                <div>
                    <span dusk="child">Child: {{ $bar }}</span>
                    <span x-text="$wire.bar" dusk="child.ephemeral"></span>
                    <button wire:click="bar++" dusk="increment">increment</button>
                </div>
                HTML; }
            },
        ])
        ->assertSeeIn('@parent', 'Parent: 0')
        ->assertSeeIn('@child', 'Child: 0')
        ->assertSeeIn('@parent.ephemeral', '0')
        ->assertSeeIn('@child.ephemeral', '0')
        ->click('@increment')
        ->assertSeeIn('@parent', 'Parent: 0')
        ->assertSeeIn('@child', 'Child: 0')
        ->assertSeeIn('@parent.ephemeral', '1')
        ->assertSeeIn('@child.ephemeral', '1')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@parent', 'Parent: 1')
        ->assertSeeIn('@child', 'Child: 1')
        ->assertSeeIn('@parent.ephemeral', '1')
        ->assertSeeIn('@child.ephemeral', '1')
        ;
    }

    public function test_can_bind_a_live_property_from_parent_to_property_from_child()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public $foo = 0;

                public function render() { return <<<'HTML'
                <div>
                    <span dusk="parent">Parent: {{ $foo }}</span>
                    <span x-text="$wire.foo" dusk="parent.ephemeral"></span>

                    <livewire:child wire:model.live="foo" />

                    <button wire:click="$refresh" dusk="refresh">refresh</button>
                </div>
                HTML; }
            },
            'child' => new class extends \Livewire\Component {
                #[BaseModelable]
                public $bar;

                public function render() { return <<<'HTML'
                <div>
                    <span dusk="child">Child: {{ $bar }}</span>
                    <span x-text="$wire.bar" dusk="child.ephemeral"></span>
                    <button wire:click="bar++;" dusk="increment">increment</button>
                </div>
                HTML; }
            },
        ])
        ->assertSeeIn('@parent', 'Parent: 0')
        ->assertSeeIn('@child', 'Child: 0')
        ->assertSeeIn('@parent.ephemeral', '0')
        ->assertSeeIn('@child.ephemeral', '0')
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@parent', 'Parent: 1')
        ->assertSeeIn('@child', 'Child: 1')
        ->assertSeeIn('@parent.ephemeral', '1')
        ->assertSeeIn('@child.ephemeral', '1')
        ;
    }

    public function test_can_bind_a_property_from_parent_array_to_property_from_child()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public $foo = ['bar' => 'baz'];

                public function render()
                {
                    return <<<'HTML'
                <div>
                    <span dusk='parent'>Parent: {{ $foo['bar'] }}</span>
                    <span x-text="$wire.foo['bar']" dusk='parent.ephemeral'></span>

                    <livewire:child wire:model='foo.bar' />

                    <button wire:click='$refresh' dusk='refresh'>refresh</button>
                </div>
                HTML;
                }
            },
            'child' => new class extends \Livewire\Component {
                #[BaseModelable]
                public $bar;

                public function render()
                {
                    return <<<'HTML'
                <div>
                    <span dusk='child'>Child: {{ $bar }}</span>
                    <span x-text='$wire.bar' dusk='child.ephemeral'></span>
                    <input type='text' wire:model='bar' dusk='child.input' />
                </div>
                HTML;
                }
            },
        ])
        ->assertDontSee('Property [$foo.bar] not found')
        ->assertSeeIn('@parent', 'Parent: baz')
        ->assertSeeIn('@child', 'Child: baz')
        ->assertSeeIn('@parent.ephemeral', 'baz')
        ->assertSeeIn('@child.ephemeral', 'baz')
        ->type('@child.input', 'qux')
        ->assertSeeIn('@parent', 'Parent: baz')
        ->assertSeeIn('@child', 'Child: baz')
        ->assertSeeIn('@parent.ephemeral', 'qux')
        ->assertSeeIn('@child.ephemeral', 'qux')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@parent', 'Parent: qux')
        ->assertSeeIn('@child', 'Child: qux')
        ->assertSeeIn('@parent.ephemeral', 'qux')
        ->assertSeeIn('@child.ephemeral', 'qux');
    }

    public function test_can_bind_a_property_from_parent_array_using_a_numeric_index_to_property_from_child()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public $foo = ['baz'];

                public function render()
                {
                    return <<<'HTML'
                <div>
                    <span dusk='parent'>Parent: {{ $foo[0] }}</span>
                    <span x-text="$wire.foo[0]" dusk='parent.ephemeral'></span>

                    <livewire:child wire:model='foo.0' />

                    <button wire:click='$refresh' dusk='refresh'>refresh</button>
                </div>
                HTML;
                }
            },
            'child' => new class extends \Livewire\Component {
                #[BaseModelable]
                public $bar;

                public function render()
                {
                    return <<<'HTML'
                <div>
                    <span dusk='child'>Child: {{ $bar }}</span>
                    <span x-text='$wire.bar' dusk='child.ephemeral'></span>
                    <input type='text' wire:model='bar' dusk='child.input' />
                </div>
                HTML;
                }
            },
        ])
        ->assertDontSee('Property [$foo.0] not found')
        ->assertSeeIn('@parent', 'Parent: baz')
        ->assertSeeIn('@child', 'Child: baz')
        ->assertSeeIn('@parent.ephemeral', 'baz')
        ->assertSeeIn('@child.ephemeral', 'baz')
        ->type('@child.input', 'qux')
        ->assertSeeIn('@parent', 'Parent: baz')
        ->assertSeeIn('@child', 'Child: baz')
        ->assertSeeIn('@parent.ephemeral', 'qux')
        ->assertSeeIn('@child.ephemeral', 'qux')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@parent', 'Parent: qux')
        ->assertSeeIn('@child', 'Child: qux')
        ->assertSeeIn('@parent.ephemeral', 'qux')
        ->assertSeeIn('@child.ephemeral', 'qux');
    }

    public function test_can_bind_a_property_from_parent_form_to_property_from_child()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public CreatePost $form;

                public function submit()
                {
                    $this->form->store();
                }

                public function render()
                {
                    return <<<'HTML'
                <div>
                    <span dusk='parent'>Parent: {{ $form->title }}</span>
                    <span x-text='$wire.form.title' dusk='parent.ephemeral'></span>

                    <livewire:child wire:model='form.title' />

                    <button wire:click='$refresh' dusk='refresh'>refresh</button>
                    <button wire:click='submit' dusk='submit'>submit</button>
                </div>
                HTML;
                }
            },
            'child' => new class extends \Livewire\Component {
                #[BaseModelable]
                public $bar;

                public function render()
                {
                    return <<<'HTML'
                        <div>
                            <span dusk='child'>Child: {{ $bar }}</span>
                            <span x-text='$wire.bar' dusk='child.ephemeral'></span>
                            <input type='text' wire:model='bar' dusk='child.input' />
                        </div>
                    HTML;
                }
            },
        ])
        ->assertDontSee('Property [$form.title] not found')
        ->assertSeeIn('@parent', 'Parent:')
        ->assertSeeIn('@child', 'Child:')
        ->assertSeeNothingIn('@parent.ephemeral')
        ->assertSeeNothingIn('@child.ephemeral')
        ->type('@child.input', 'foo')
        ->assertSeeIn('@parent', 'Parent:')
        ->assertSeeIn('@child', 'Child:')
        ->assertSeeIn('@parent.ephemeral', 'foo')
        ->assertSeeIn('@child.ephemeral', 'foo')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@parent', 'Parent: foo')
        ->assertSeeIn('@child', 'Child: foo')
        ->assertSeeIn('@parent.ephemeral', 'foo')
        ->assertSeeIn('@child.ephemeral', 'foo')
        ->waitForLivewire()->click('@submit')
        ->assertSeeNothingIn('@parent.ephemeral', '')
        ->assertSeeNothingIn('@child.ephemeral', '')
        ;
    }
}

class CreatePost extends Form
{
    #[Validate('required')]
    public $title;

    public function store()
    {
        Post::create($this->all());

        $this->reset();
    }
}

class Post extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'title' => 'foo'],
        ['id' => 2, 'title' => 'bar'],
    ];

    protected $fillable = ['title'];
}
